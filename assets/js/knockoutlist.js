/**
 * Created by damian on 30/11/14.
 */

ko.bindingHandlers.stopBinding = {
    init: function() {
        return { controlsDescendantBindings: true };
    }
};
ko.virtualElements.allowedBindings.stopBinding = true;

//Private scope to prevent extending of Model in other widgets.
(function () {
    var Instance = function(data) {

        var ItemModel = function (data) {
            var self = this;
            this.loadedData = data;
            $.each(data, function (key, item) {
                if (!$.isArray(item) && typeof item !== 'object') {
                    self[key] = ko.observable(item);
                }
            });
            this.id = ko.observable(data.id);
            if (typeof this.extend === 'function') {
                this.extend();
            }
        };

        var ListView = function (data) {
            var self = this;
            this.init = function (data) {
                this.id ? this.id(data.id) : this.id = ko.observable(data.id);
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
            this.init(data);
            this.changePage = function (data, event) {
                var url = $(event.target).attr('href');
                $.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'json',
                    data: {
                        ajax: self.id()
                    },
                    success: function (res) {
                        self.init(res);
                    }
                });
            };
        };

        if (typeof data.extend === 'string') {
            eval(data.extend);
        }

        return new ListView(data);
    };
    yii.knockoutlist = (function ($) {
        var pub = {
            listView: {},
            setup: function (data) {
                this.listView[data.id] = new Instance(data);
                if (data.applyBindings) {
                    ko.applyBindings(this.listView[data.id], $('#' + data.id)[0]);
                }

            }
        };
        return pub;
    })(jQuery);
    jQuery(document).ready(function () {
        yii.initModule(yii.knockoutlist);
    });
})();