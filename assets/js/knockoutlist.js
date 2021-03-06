/**
 * Created by damian on 30/11/14.
 */

ko.bindingHandlers.stopBinding = {
    init: function() {
        return { controlsDescendantBindings: true };
    }
};
ko.virtualElements.allowedBindings.stopBinding = true;

window.history.pushState = window.history.pushState || function() {};

//Private scope to prevent extending of Model in other widgets.
(function () {
    var Instance = function(data) {

        var instanceId = data.id;

        var ItemModel = function (data) {
            var self = this;
            this.loadedData = data;

            if (yii.knockoutlist.settings[instanceId].autoObservables) {
                $.each(data, function (key, item) {
                    if (!$.isArray(item)) {
                        self[key] = ko.observable(item);
                    }
                    else if ($.isArray(item)) {
                        self[key] = ko.observableArray(item);
                    }
                });
            }

            this.actions ? this.actions(data.actions) : this.actions = ko.observable(data.actions);

            if (typeof this.extend === 'function') {
                this.extend();
            }
        };

        var ListView = function (data) {
            var self = this;
            var usePushState = data.usePushState || false;
            this.searchData = {};
            this.init = function (data) {
                this.loadedData = data;
                this.id ? this.id(data.id) : this.id = ko.observable(data.id);
                this.labels ? this.labels(data.labels) : this.labels = ko.observable(data.labels);
                this.begin ? this.begin(data.begin) : this.begin = ko.observable(data.begin);
                this.end ? this.end(data.end) : this.end = ko.observable(data.end);
                this.totalCount ? this.totalCount(data.totalCount) : this.totalCount = ko.observable(data.totalCount);
                this.pageCount ? this.pageCount(data.pageCount) : this.pageCount = ko.observable(data.pageCount);
                if (typeof data.sort === 'object') {
                    this.sort ? this.sort(data.sort) : this.sort = ko.observableArray(data.sort);
                }
                var items = ko.utils.arrayMap(data.items, function (item) {
                    return new ItemModel(item);
                });
                this.items ? this.items(items) : this.items = ko.observableArray(items);
                if (typeof data.pageLinks === 'object') {
                    this.selfPage ? this.selfPage(data.pageLinks.self) : this.selfPage = ko.observable(data.pageLinks.self);
                    this.firstPage ? this.firstPage(data.pageLinks.first) : this.firstPage = ko.observable(data.pageLinks.first);
                    this.prevPage ? this.prevPage(data.pageLinks.prev) : this.prevPage = ko.observable(data.pageLinks.prev);
                    this.nextPage ? this.nextPage(data.pageLinks.next) : this.nextPage = ko.observable(data.pageLinks.next);
                    this.lastPage ? this.lastPage(data.pageLinks.last) : this.lastPage = ko.observable(data.pageLinks.last);
                }
                if (typeof data.pages === 'object') {
                    this.pages ? this.pages(data.pages) : this.pages = ko.observableArray(data.pages);
                }
                if (typeof this.extend === 'function') {
                    this.extend();
                }
            };
            this.changePage = function (data, event) {
                var url = $(event.target).attr('href');
                var send = {};
                if (!url.match(/(\?|&)ajax=/)) {
                    send.ajax = self.id();
                }
                $.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'json',
                    data: send,
                    success: function (res) {
                        self.init(res);
                        if (yii.knockoutlist.settings[self.id()].usePushState) {
                            url = url.replace(/\?ajax=[^&]+&/, '?');
                            window.history.pushState({id: self.id(), json: res}, "", url);
                        }
                    }
                });
            };
            this.searchList = function(items, clear) {

                if (typeof clear !== 'undefined' && clear) {
                    self.searchData = {};
                }

                $.extend(self.searchData, items);

                $.ajax({
                    url: yii.knockoutlist.currentUrl,
                    type: 'get',
                    dataType: 'json',
                    data: $.extend({ajax: self.id()}, self.searchData),
                    success: function (res) {
                        self.init(res);
                        if (yii.knockoutlist.settings[self.id()].usePushState) {
                            url = url.replace(/\?ajax=[^&]+&/, '?');
                            window.history.pushState({id: self.id(), json: res}, "", url);
                        }
                    }
                });
            };
            this.init(data);
        };

        if (typeof data.extendModels === 'function') {
            data.extendModels(ListView, ItemModel);
        }

        return new ListView(data);
    };
    yii.knockoutlist = (function ($) {
        var pub = {
            listView: {},
            settings: {},
            currentUrl: "",
            loadData: function (data, applyBindings) {

                yii.knockoutlist.listView[data.id] = new Instance(data);
                if (applyBindings) {
                    var id = $('#' + data.id)[0];
                    ko.cleanNode(id);
                    ko.applyBindings(yii.knockoutlist.listView[data.id], id);
                }
            },
            setup: function (data) {
                var self = this;
                self.currentUrl = window.location.href;
                this.settings[data.id] = {};
                this.settings[data.id].applyBindings = data.applyBindings || false;
                this.settings[data.id].autoObservables = data.autoObservables || false;
                this.settings[data.id].usePushState = data.usePushState || false;

                if (typeof data.async !== 'undefined') {
                    $.ajax({
                        url: self.currentUrl,
                        type: 'get',
                        dataType: 'json',
                        data: {
                            ajax: data.id
                        },
                        success: function (res) {
                            self.loadData(res, self.settings[data.id].applyBindings);
                            if (self.settings[data.id].usePushState) {
                                window.history.pushState({id: data.id, json: res}, "", self.currentUrl);
                            }
                        }
                    });
                }
                else {
                    self.loadData(data, this.settings[data.id].applyBindings);
                    if (self.settings[data.id].usePushState) {
                        window.history.pushState({id: data.id, json: data}, "", self.currentUrl);
                    }
                }
                if (this.settings[data.id].usePushState) {
                    window.onpopstate = function(e) {
                        if (e.state && typeof yii.knockoutlist.listView[e.state.id] != 'undefined') {
                            yii.knockoutlist.listView[e.state.id].init(e.state.json);
                        }
                    };
                }
            }
        };
        return pub;
    })(jQuery);
    jQuery(document).ready(function () {
        yii.initModule(yii.knockoutlist);
    });
})();