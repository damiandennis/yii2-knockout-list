/**
 * Created by damian on 30/11/14.
 */

ko.bindingHandlers.stopBinding = {
    init: function() {
        return { controlsDescendantBindings: true };
    }
};
ko.virtualElements.allowedBindings.stopBinding = true;

yii.knockoutlist = (function ($) {
    var ItemModel = function(data) {
        this.id = ko.observable(data.id);
    };
    var ListView = function(data) {
        var self = this;
        this.init = function(data) {
            this.id ? this.id(data.id) : this.id = ko.observable(data.id);
            this.begin ? this.begin(data.begin) : this.begin = ko.observable(data.begin);
            this.end ? this.end(data.end) : this.end = ko.observable(data.end);
            this.totalCount ? this.totalCount(data.totalCount) : this.totalCount = ko.observable(data.totalCount);
            var items = ko.utils.arrayMap(data.items, function(item) {
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
        };
        this.init(data);
        this.changePage = function(data, event) {
            var url = $(event.target).attr('href');
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: {
                    ajax: self.id()
                },
                success: function(res) {
                    self.init(res, true);
                }
            });
        };
    };
    var pub = {
        listView: {},
        setup: function(data) {
            this.listView[data.id] = new ListView(data);
            if (data.applyBindings) {
                ko.applyBindings(this.listView[data.id], $('#'+data.id)[0]);
            }
        }
    };
    return pub;
})(jQuery);
jQuery(document).ready(function () {
    yii.initModule(yii.knockoutlist);
});