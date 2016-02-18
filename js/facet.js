(function ($) {
  Drupal.behaviors.ctSearchFacets = {
    attach: function (context) {
      bindFacetLink(context, '.ctsearch-facet a.see-more');
    }

  };

  function bindFacetLink(context, selector){
    $(selector, context).click(function(e){
      e.preventDefault();
      var id = $(this).parents('.ctsearch-facet').attr('id');
      var facet = $(this).parents('.ctsearch-facet');
      var link = $(this);
      $('<div class="ctsearch-throbber">&nbsp;</div>').insertAfter(link);
      $.ajax({
        url: $(this).attr('href')
      }).success(function(data){
        facet.html($(data).find('#' + id).html());
        facet.find('.ctsearch-throbber').detach();
        bindFacetLink(context, '.ctsearch-facet#' + id + ' a.see-more');
      });
      return false;
    });
  }

})(jQuery);