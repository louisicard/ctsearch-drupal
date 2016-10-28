(function ($) {
  $(document).ready(function(){
    bindFacetLink('.ctsearch-facet a.see-more');
  });

  function bindFacetLink(selector){
    $(selector).click(function(e){
      e.preventDefault();
      var id = $(this).parents('.ctsearch-facet').attr('id');
      var facet = $(this).parents('.ctsearch-facet');
      var link = $(this);
      $('<div class="ctsearch-throbber">&nbsp;</div>').insertAfter(link);

      $.ajax({
        url: link.attr('href'),
        method: 'get',
        success: function(data){
          facet.html($(data).find('#' + id).html());
          facet.find('.ctsearch-throbber').detach();
          bindFacetLink('.ctsearch-facet#' + id + ' a.see-more');
        }
      });
      return false;
    });
  }

})(jQuery);