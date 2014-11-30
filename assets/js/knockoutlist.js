/**
 * Created by damian on 30/11/14.
 */
yii.knockoutlist = (function ($) {
    var ListView = function(items) {
        this.data = ko.observableArray(data.items);
    };
    var pub = {
        listView: {},
        docReady: function(data) {
            this.listView = new ListView(data);
        }
    };
    return pub;
})(jQuery);
jQuery(document).ready(function () {
    yii.initModule(yii.knockoutlist);
});