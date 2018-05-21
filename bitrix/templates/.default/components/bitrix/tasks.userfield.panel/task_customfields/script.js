$(function() {

	loadCurrency();

	$('input.calc_rendement').on('keyup', function() {
		var clean = this.value.replace(/[^0-9,.]/g, "")
	                           .replace(/(,.*?),(.*,)?/, "$1");

	    // don't move cursor to end if no change
	    if (clean !== this.value) this.value = clean;
	});

	$('input.calc_money').on('change', function() {
		var value = cleanValue($(this).val());

		if($(this).val() != "") {
			var finalValue = value;
			$(this).val(formatValue(finalValue));
		}
	});

	$('input.calc_rendement').on('change', function() {

		var value = cleanValue($(this).val());

		if($(this).val() != "") {
			var finalValue = value;
			$(this).val(formatValue(finalValue));
		}

		//.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		calculate();
	});

	function calculate() {

		var total = 0;
		$('input.calc_rendement').each(function(i) {

			var _money = cleanValue($(this).val());

			if(_money > 0) {
				if(i == 0) total = _money;
				else total = total - _money;
			}
		});

		var finalVal = total;
		$('input.rendement_diff').val(formatValue(finalVal));
	}

	function loadCurrency() {

		$('input.calc_rendement, input.rendement_diff').each(function() {

			var span = $("<span>").addClass("task_currency").html("€");

			$(this)
				.parent()
				.prepend(span);
		});
	}	

	function formatValue(value) {

		var newValue = parseFloat(value).toFixed(2)
					   .replace(".", ",")
					   .replace(/\B(?=(\d{3})+(?!\d))/g, ".");

		return (newValue);
	}
	function cleanValue(value) {

		var newValue = value.replace(/\./g,'').replace(",", ".");
		return (parseFloat(newValue).toFixed(2));
	}

	$('span.add_dropdown_text').on('click', function() {

		var addId = $(this).attr('id').replace("ADD_", "");
		var container = $("div#CONT_" + addId);
		var hidden = $("div#HIDDEN_" + addId);
		var fieldText = $(hidden).find("input[name='"+addId+"_multi_field_text']").val();
		var fieldDropdown = $(hidden).find("input[name='"+addId+"_multi_field_dropdown']").val();
		var fieldDropdownList = $(hidden).find("input[name='"+addId+"_multi_field_dropdown_list']").val();


		var divMoreField = $('<div>').addClass('tasks-uf-panel-row-data-more-field');
		var divMoreFieldDrop = $('<div>').addClass('tasks-uf-panel-row-data-more-dropdown');
		var divMoreFieldvalue = $('<div>').addClass('tasks-uf-panel-row-data-more-value');

		if(fieldDropdownList != "") {

			var select = $('<select>').addClass('kosten_more_info').attr('name', fieldDropdown);
			var fieldDropdownListObj = JSON.parse(fieldDropdownList);
			$.each( fieldDropdownListObj, function( key, value ) {

				var selectOpt = $('<option>').attr('value', key).html(value);
				
				selectOpt.appendTo(select);
			});

			select.appendTo(divMoreFieldDrop);
		}

		var fieldContainer = $('<div>').addClass('fields string');
		var cur = $('<span>').addClass('task_currency').html('€');

		// get last tabindex;
		var lastInputTab = $('input.calc_money').last().attr('tabindex');

		if(parseInt(lastInputTab) > 0) {
			var NextInputTab = parseInt(lastInputTab) + 1;
		}
		else {
			var lastInputTab = $('input.calc_rendement').last().attr('tabindex');
			var NextInputTab = parseInt(lastInputTab) + 1;
		}

		var text = $('<input>').addClass('fields string calc_money')
							   .attr('type', 'text')
							   .attr('name', fieldText)
							   .attr('size', '20')
							   .attr('tabindex', NextInputTab)
							   .change(function() {

							   		var value = cleanValue($(this).val());

									if($(this).val() != "") {
										var finalValue = value;
										$(this).val(formatValue(finalValue));
									}
							   });

		var textDelete = $('<span>').addClass('delete-dropdown-text')
								    .attr('title', 'Delete Item')
								    .click(function() {
								    	// delete Item
										$(this).parent().parent().parent().remove();
								    });

		cur.appendTo(fieldContainer);
		text.appendTo(fieldContainer);
		textDelete.appendTo(fieldContainer);
		fieldContainer.appendTo(divMoreFieldvalue);

		divMoreFieldDrop.appendTo(divMoreField);
		divMoreFieldvalue.appendTo(divMoreField);

		divMoreField.appendTo(container);

		console.log(fieldDropdownList);
		return false;
	});

	$('span.delete-dropdown-text').on('click', function() {

		// delete Item
		$(this).parent().parent().parent().remove();
	});
});