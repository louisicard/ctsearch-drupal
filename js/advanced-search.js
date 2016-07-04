(function($){

  $(document).ready(function(){
    $('.block-ctsearch-advanced-search-block form').each(function(){
      if($(this).find('input[name="advanced_search_fields"]').val() != ""){
        var fields = $(this).find('input[name="advanced_search_fields"]').val();
        var selectValues = [];
        for(var i = 0; i < fields.split(',').length; i++){
          var specs = fields.split(',')[i].split('|');
          if(specs.length == 2){
            var field = fields.split(',')[i].split('|')[0].trim();
            var label = fields.split(',')[i].split('|')[1].trim();
            selectValues[selectValues.length] = {
              "field": field,
              "label": label
            };
          }
        }
        var lblSelectElem = $('<label for="adv-field-1">'+ Drupal.t('Field') + '</label>');
        var selectElem = $('<select id="adv-field-1"></select>');
        selectElem.addClass('advanced-search-field-selector');
        for(var i = 0; i < selectValues.length; i++){
          var optionElem = $('<option></option>')
          optionElem.html(selectValues[i].label);
          optionElem.attr('value', selectValues[i].field);
          selectElem.append(optionElem);
        }
        var container = $('<div id="advanced-search-container"></div>');
        var item = $('<div class="advanced-search-item"></div>');
        item.append(lblSelectElem);
        item.append(selectElem);
        var lblInput = $('<label for="adv-value-1">'+ Drupal.t('Value') + '</label>');
        var input = $('<input type="text" class="advanced-search-item-input" id="adv-value-1" />');
        item.append(lblInput);
        item.append(input);
        var removeLink = $('<a href="#" class="remove-item" title="' + Drupal.t('Remove') + '">' + Drupal.t('Remove') + '</a>');
        item.append(removeLink);
        container.append(item);
        container.hide();
        container.insertAfter($(this).find('div.form-item-query'));
        container.attr('item-spec', container.html());
        var toggle = $('<a href="#" class="toggle-advanced" title="' + Drupal.t('More criteria') + '">' + Drupal.t('More criteria') + '</a>');
        toggle.insertBefore(container);
        toggle.wrap('<div class="toggle-wrapper"></div>');
        toggle.click(function(e){
          e.preventDefault();
          container.slideToggle();
        });

        var activeFilters = JSON.parse($(this).find('input[name="advanced_query_json"]').val());
        if(activeFilters.length > 0) {
          container.show();
          container.html('');
          for (var i = 0; i < activeFilters.length; i++) {
            var incr = container.find('.advanced-search-item').size() + 1;
            var activeItem = $(container.attr('item-spec'));
            activeItem.find('select').val(activeFilters[i].field);
            activeItem.find('select').attr('id', 'adv-field-' + incr);
            activeItem.find('select').prev().attr('for', 'adv-field-' + incr);
            activeItem.find('input').val(activeFilters[i].value);
            activeItem.find('input').attr('id', 'adv-value-' + incr);
            activeItem.find('input').prev().attr('for', 'adv-value-' + incr);
            container.append(activeItem);
          }
        }

        var addItem = $('<a href="#" class="add-item" title="' + Drupal.t('Add a criterion') + '">' + Drupal.t('Add a criterion') + '</a>');
        container.append(addItem);
        addItem.click(function(e){
          e.preventDefault();
          var incr = container.find('.advanced-search-item').size() + 1;
          $(container.attr('item-spec').replace(/adv\-field\-1/g, 'adv-field-' + incr).replace(/adv\-value\-1/g, 'adv-value-' + incr)).insertBefore($(this));
          bindRemoveLink();
        });
      }
      bindRemoveLink();
      $(this).submit(function () {
        var advFilters = [];
        $(this).find('.advanced-search-item').each(function(){
          var field = $(this).find('select').val().trim();
          var value = $(this).find('input').val().trim();
          if(value != ''){
            advFilters[advFilters.length] = {
              "field": field,
              "value": value
            };
          }
        });
        $(this).find('input[name="advanced_query_json"]').val(JSON.stringify(advFilters));
        return true;
      })
    });
  });

  function bindRemoveLink(){
    $('.block-ctsearch-advanced-search-block form').each(function(){
      $(this).find('.advanced-search-item a.remove-item').unbind('click');
      $(this).find('.advanced-search-item a.remove-item').click(function(){
        $(this).parents('.advanced-search-item').detach();
      })
    });
  }

})(jQuery);