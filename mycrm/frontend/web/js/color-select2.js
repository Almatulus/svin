var formatRepoSelection = function (item) {
    var text = item.element.innerHTML;
    var backColor = item.element.getAttribute("back-color");
    var fontColor = item.element.getAttribute("font-color");
    var ans = "<span class='select2-selection__choice__name' style='background-color: " + backColor + "; color: " + fontColor + "'><i class='fa fa-tag'></i> " + text + "</span>";
    return ans;
}