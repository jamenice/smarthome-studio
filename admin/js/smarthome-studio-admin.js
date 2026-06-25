/**
 * This script enhances the functionality of a Jwsthemes header, footer builder.
 * It relies on jQuery for DOM manipulation and event handling.
 * 
 * Functions are organized to handle specific parts of the page interaction:
 * - Initialization of dynamic select elements
 * - Visibility control of certain UI elements
 * - Handling user interactions like clicks and changes in the form
 * 
 * Error handling and optimization have been considered in the function implementations.
 */
(function ($) {
	'use strict';

	// Throttle function to limit the rate at which a function can fire.
	function throttle(func, limit) {
		let lastFunc;
		let lastRan;
		return function () {
			const context = this;
			const args = arguments;
			if (!lastRan) {
				func.apply(context, args);
				lastRan = Date.now();
			} else {
				clearTimeout(lastFunc);
				lastFunc = setTimeout(function () {
					if ((Date.now() - lastRan) >= limit) {
						func.apply(context, args);
						lastRan = Date.now();
					}
				}, limit - (Date.now() - lastRan));
			}
		}
	}

	// Debounce function to postpone a function's execution until after a delay.
	function debounce(func, delay) {
		let debounceTimer;
		return function () {
			const context = this;
			const args = arguments;
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => func.apply(context, args), delay);
		};
	}

	// Initializes a dynamic select element using Select2 with AJAX.
	const initializeDynamicSelect = (selector) => {
		const ajaxOptions = {
			url: ajaxurl,
			dataType: 'json',
			method: 'post',
			delay: 250,
			data: (params) => ({
				q: params.term, // search term
				page: params.page,
				action: 'jws_retrieve_posts_based_on_query',
				nonce: admin.nonce
			}),
			processResults: (data) => ({
				results: data
			}),
			cache: true
		};

		const select2Options = {
			placeholder: admin.search,
			ajax: ajaxOptions,
			minimumInputLength: 2,
			language: admin.language
		};

		try {
			$(selector).select2(select2Options);
		} catch (error) {
			console.error('Error initializing dynamic select:', error);
		}
	};

	// Updates the visibility of the close button based on certain conditions.
	const updateCloseButtonVisibility = (wrap) => {
		const dataType = wrap.closest('.jws-fields_container').attr('data-type');
		const rules = wrap.find('.jws-rule_condition_block');
		let shouldShowClose = dataType === 'display' ? rules.length > 1 : true;

		rules.each((index, rule) => {
			const deleteIcon = $(rule).find('.jws-delete_rule_icon');
			shouldShowClose ? deleteIcon.removeClass('hidden') : deleteIcon.addClass('hidden');
		});
	};


	// Handles page-specific logic when the document is ready.
	$(document).ready(function ($) {

		const updateFieldVisibility = () => {
			const selectedTemplateType = $('#jws_template_type').val() || 'none';
			const selectedBlockHooks = $('#jws_block_hooks').val() || '';
			const optionsTable = $('.smarthome-jws-options-table');
			const blockShortcodeRow = $('.jws-row.jws-shortcode-row');
			const blockHook = $('.jws-row.jws-block-hook');
			const rowRules = $('.jws-row.jws-row-rules');
			const rowRulesExclude = $('.jws-row.jws-row-rules-exclude');
			const excludeOnWrap = $('.jws-exclude-on-wrap');
			const excludeFieldWrap = excludeOnWrap.closest('tr');

			// Handle visibility of the options table
			if (['none', 'tmp_megamenu'].includes(selectedTemplateType)) {
				optionsTable.addClass('jws-options-none');
			} else {
				optionsTable.removeClass('jws-options-none');
			}

			// Handle visibility of the custom block row and jws-row-rules
			if (selectedTemplateType === 'tmp_custom_block') {
				blockHook.show();
				blockShortcodeRow.show(); // Show by default when 'tmp_custom_block' is selected, later logic may hide it
				rowRules.show();
			} else {
				blockHook.hide();
				blockShortcodeRow.hide();
				// Show jws-row-rules if template type is not empty or 'tmp_megamenu'
				if (selectedTemplateType !== 'none' && selectedTemplateType !== 'tmp_megamenu' && selectedTemplateType !== 'tmp_slider') {
					rowRules.show();
				} else {
					rowRules.hide();
				}
			}

			// Additional condition for 'jws_block_hooks' value 'shortcode'
			if (selectedBlockHooks === 'shortcode' && selectedTemplateType === 'tmp_custom_block') {
				blockShortcodeRow.show();
				rowRules.hide(); // Hide jws-row-rules when 'shortcode' is selected
				excludeFieldWrap.addClass('hidden');
			} else {
				blockShortcodeRow.hide();
				excludeFieldWrap.removeClass('hidden');
			}
		};

		// Attach event handler for changes in template type and block hooks selection.
		$(document).on('change', '#jws_template_type, #jws_block_hooks', () => {
			updateFieldVisibility();
		});

		// Initialize field visibility on document load.
		$(document).ready(() => {
			updateFieldVisibility();
		});



		jQuery('.jws-rule_condition_block').each((index, element) => {
			const ruleBlock = jQuery(element);
			const condition = ruleBlock.find('select.jws-selection_dropdown');
			const conditionValue = condition.val();
			const specificPageWrap = ruleBlock.next('.jws-targeted-page-wrap');

			if (conditionValue === 'specifics') {
				specificPageWrap.slideDown(300);
			}
		});

		// Initialize select elements with dynamic content.
		jQuery('select.jws-targeted-select2').each((index, element) => {
			initializeDynamicSelect(element);
		});

		// Update the visibility of close buttons in selector containers.
		jQuery('.jws-selector_container').each((index, container) => {
			updateCloseButtonVisibility(jQuery(container));
		});

		// Update the exclusion button visibility based on certain conditions.
		const updateExclusionButtonVisibility = (forceShow = false, forceHide = false) => {
			const displayOnWrap = $('.jws-display-on-wrap');
			const excludeOnWrap = $('.jws-exclude-on-wrap');
			const excludeFieldWrap = excludeOnWrap.closest('tr');
			const addExcludeBlock = displayOnWrap.find('.jws-create_exclusion_rule');
			const excludeConditions = excludeOnWrap.find('.jws-rule_condition_block');
			const rowRulesExclude = $('.jws-row.jws-row-rules-exclude');

			if (forceHide) {
				excludeFieldWrap.addClass('hidden');
				excludeFieldWrap.hide();
				addExcludeBlock.removeClass('hidden');
			} else if (forceShow) {
				excludeFieldWrap.removeClass('hidden');
				excludeFieldWrap.show();
				addExcludeBlock.addClass('hidden');
			} else {
				const isSingleEmptyCondition = excludeConditions.length === 1 &&
					$(excludeConditions[0]).find('select.jws-selection_dropdown').val() === '';
				if (isSingleEmptyCondition) {
					excludeFieldWrap.addClass('hidden');
					excludeFieldWrap.hide();
					addExcludeBlock.removeClass('hidden');
				} else {
					excludeFieldWrap.removeClass('hidden');
					excludeFieldWrap.show();
					addExcludeBlock.addClass('hidden');
				}
			}
		};
		updateExclusionButtonVisibility();

		// Update the target rule input based on user selection.
		const updateTargetRuleInput = (wrapper) => {
			let newValues = [];

			wrapper.find('.jws-rule_condition_block').each((index, element) => {
				const ruleCondition = $(element).find('select.jws-selection_dropdown');
				const specificPage = $(element).find('select.jws-targeted-page');
				const ruleConditionValue = ruleCondition.val();
				const specificPageValue = specificPage.val();

				if (ruleConditionValue !== '') {
					newValues.push({
						type: ruleConditionValue,
						specific: specificPageValue
					});
				}
			});
		};

		jQuery(document).on('change', '.jws-rule_condition_block select.jws-selection_dropdown', (event) => {
			const selectedDropdown = jQuery(event.currentTarget);
			const selectedValue = selectedDropdown.val();
			const fieldContainer = selectedDropdown.closest('.jws-fields_container');
			const targetedPageWrap = selectedDropdown.closest('.jws-rule_condition_block').next('.jws-targeted-page-wrap');

			if (selectedValue === 'specifics') {
				targetedPageWrap.slideDown(300);
			} else {
				targetedPageWrap.slideUp(300);
			}

			updateTargetRuleInput(fieldContainer);
		});

		jQuery('.jws-selector_container').on('change', '.jws-targeted-select2', (event) => {
			const selectedElement = jQuery(event.currentTarget);
			const fieldContainer = selectedElement.closest('.jws-fields_container');

			updateTargetRuleInput(fieldContainer);
		});

		jQuery('.jws-selector_container').on('click', '.jws-delete_rule_icon', (event) => {
			const clickedIcon = jQuery(event.currentTarget);
			const ruleConditionBlock = clickedIcon.closest('.jws-rule_condition_block');
			const fieldContainer = clickedIcon.closest('.jws-fields_container');
			const dataType = fieldContainer.attr('data-type');

			if (dataType === 'exclude') {
				if (fieldContainer.find('.jws-selection_dropdown').length === 1) {
					const dropdown = fieldContainer.find('.jws-selection_dropdown');
					dropdown.val('').trigger('change');
					fieldContainer.find('.jws-targeted-page').val('');
					updateExclusionButtonVisibility(false, true);
				} else {
					removeRuleBlock(clickedIcon);
				}
			} else {
				removeRuleBlock(clickedIcon);
			}

			let ruleCount = 0;
			fieldContainer.find('.jws-rule_condition_block').each((index, element) => {
				const condition = jQuery(element);
				updateRuleAttributes(condition, index);
				ruleCount = index;
			});

			fieldContainer.find('.jws-create_new_rule a').attr('data-rule-id', ruleCount);

			updateCloseButtonVisibility(fieldContainer);
			updateTargetRuleInput(fieldContainer);
		});

		function removeRuleBlock(clickedIcon) {
			clickedIcon.parent('.jws-rule_condition_block').next('.jws-targeted-page-wrap').remove();
			clickedIcon.closest('.jws-rule_condition_block').remove();
		}

		function updateRuleAttributes(condition, index) {
			const selectDropdown = condition.find('.jws-selection_dropdown');
			const selectSpecific = condition.find('.jws-targeted-page');
			const oldRuleId = condition.attr('data-rule');
			const locationName = selectDropdown.attr('name');

			condition.attr('data-rule', index);
			selectDropdown.attr('name', locationName.replace(`[${oldRuleId}]`, `[${index}]`));
			condition.removeClass(`jws-rule-${oldRuleId}`).addClass(`jws-rule-${index}`);
		}

		jQuery('.jws-selector_container').on('click', '.jws-create_new_rule a', (event) => {
			event.preventDefault();
			event.stopPropagation();

			const clickedElement = jQuery(event.currentTarget);
			const ruleId = parseInt(clickedElement.attr('data-rule-id'), 10);
			const newRuleId = ruleId + 1;
			const ruleType = clickedElement.attr('data-rule-type');
			const ruleWrap = clickedElement.closest('.jws-selector_container').find('.jws-fields_builder_wrap');
			const ruleTemplate = wp.template(`jws-${ruleType}-condition`);
			const fieldContainer = clickedElement.closest('.jws-fields_container');

			ruleWrap.append(ruleTemplate({ id: newRuleId, type: ruleType }));

			initializeDynamicSelect(`.jws-${ruleType}-on .jws-targeted-select2`);

			clickedElement.attr('data-rule-id', newRuleId);

			updateCloseButtonVisibility(fieldContainer);
		});

		jQuery('.jws-selector_container').on('click', '.jws-create_exclusion_rule a', (event) => {
			event.preventDefault();
			event.stopPropagation();
			updateExclusionButtonVisibility(true);
		});


	});


})(jQuery);