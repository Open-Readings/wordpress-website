jQuery(window).on('elementor/frontend/init', function() {

  var coll = document.getElementsByClassName("collapsible");
  var i;
  for (i = 0; i < coll.length; i++) {
      coll[i].addEventListener("click", function() {
        var content = this.nextElementSibling;
        var arrows = this.querySelector('.arrow');
        if (content.style.maxHeight){
          content.style.maxHeight = null;
          arrows.style.transform = 'rotate(0deg)'
        } else {
          content.style.maxHeight = content.scrollHeight + "px";
          arrows.style.transform = 'rotate(90deg)'
        }
      });
  }
});