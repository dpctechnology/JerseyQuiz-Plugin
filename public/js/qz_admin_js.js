jQuery(document).ready(function ($) {

    /**
     * Questions JS 
     * 
     * */

    // Multiple options listing
    let prev_options = '';
    $('#question-type input').on('change', function () {
        let _this = $(this);
        let type = _this.val();

        if (type === 'multiple') {
            if (prev_options) {
                _this.parent().parent().append(prev_options);
            }
            else {
                _this.parent().parent().append(`
                    <div class="multiple-options">
                        <label>
                            <input type="text" name="multiple_options[]" value="" required />
                        </label>
                        <label>
                            <input type="text" name="multiple_options[]" value="" required />
                        </label>
                        <div class="add-question">
                            <a href="javascript:void(0)">Add New <i class="fa fa-plus"></i></a>
                        </div>
                    </div>
                `);
            }
        }
        else {
            prev_options = _this.parent().parent().find('.multiple-options').clone();
            _this.parent().parent().find('.multiple-options').remove();
        }
    });

    // Add question
    $(document).on('click', '.add-question a', function () {
        let _this = $(this);

        if (_this.parent().parent().find('label').length < 6) {
            $(`<label>
                <input type="text" name="multiple_options[]" value="" required />
                <a href='javascript:void(0)' class="remove-question"><i class="fa fa-minus"></i></a>
            </label>`).insertBefore(_this.parent());
        }

        if (_this.parent().parent().find('label').length === 6) {
            _this.parent().remove();
        }
    });

    // Remove question
    $(document).on('click', '.remove-question', function () {
        let _this = $(this);

        if (_this.parent().parent().find('label').length <= 6 && _this.parent().parent().find('.add-question').length === 0) {
            _this.parent().parent().append(`<div class="add-question">
                                                <a href="javascript:void(0)">Add New <i class="fa fa-plus"></i></a>
                                            </div>`);
        }

        _this.parent().remove();
    });


    /**
     * Quiz JS 
     * 
     * */

    // function get_questions_ajax() {
    // }

    let quiz_json = [];

    // Question selection functionality
    $(document).on('change', '.question-select', function () {
        let _this = $(this);
        let posttype = _this.data('posttype');
        let question_id = _this.val();
        let hierarchy_index = _this.data('index');
        let ques_name = _this.attr('name');
        let newObj = {};

        _this.find('option').removeAttr('selected');
        _this.children().each(function (i, v) {
            if (question_id == $(v).val())
                $(v).attr('selected', 'selected').prop('selected', true);

        });


        $.ajax({
            type: 'post',
            cache: false,
            url: URLs.AJAX_URL,
            data: {
                action: URLs.PLUGIN_PREFIX + "_get_questions",
                posttype: posttype,
                question_id: question_id
            },
            success: function (res) {
                res = JSON.parse(res);
                if ((res.status === true || res.status === 1) && res.data != undefined) {
                    let data = res.data;
                    let condition = res.condition;
                    let ques_template = '';
                    let opt_template = '<div class="conditions">';

                    _this.parent().find('.conditions').remove();

                    // Remove question from conditions select at last question selection 
                    if (_this.parent().find('option').length == 2)
                        condition.splice(0, 1);

                    // Append conditions
                    let cd_count = 1;
                    for (const key in data) {
                        if (Object.hasOwnProperty.call(data, key)) {
                            const ques = data[key];
                            opt_template += '<div>';
                            opt_template += '<span>';
                            opt_template += ques;
                            opt_template += ': ';
                            opt_template += '</span>';
                            opt_template += '<select class="conditions-select" data-name="' + get_key(ques_name) + '[condition][cd_' + cd_count + ']" data-cd_index="' + cd_count + '" required>';
                            opt_template += '<option value="">Select condition...</option>';

                            for (const condition_key in condition) {
                                if (Object.hasOwnProperty.call(condition, condition_key)) {
                                    const elements = condition[condition_key];
                                    opt_template += '<option value="' + elements.type + '">';
                                    opt_template += elements.title;
                                    opt_template += '</option>';
                                }
                            }

                            opt_template += '</select>';
                            opt_template += '</div>';
                        }
                        cd_count++;
                    }
                    opt_template += '</div>';
                    _this.parent().append(opt_template);
                }
                else {
                    _this.next().remove();
                }
            }
        });
    });

    function get_key(previous_key) {
        var new_key = previous_key.replace('[id]', '');
        new_key = new_key.replace('[qs_id]', '');
        return new_key;
    }
    // Condition based options
    $(document).on('change', '.conditions-select', function () {
        let _this = $(this);
        let question_type = URLs.PLUGIN_PREFIX + '-questions';
        let value = _this.val();
        let ques_template = '';
        let prev_hierarchy_index = _this.parent().parent().prev().data('index');
        let next_hierarchy_index = parseInt(prev_hierarchy_index) + 1;
        let cd_index = _this.data('cd_index');
        let cd_name = _this.data('name');

        _this.find('option').removeAttr('selected');
        _this.children().each(function (i, v) {
            if (value == $(v).val())
                $(v).attr('selected', 'selected').prop('selected', true);

        });

        _this.next().remove();

        if (question_type === value) {
            let ques_clone = $(this).parent().parent().prev().clone();
            let selected_val = $(this).parent().parent().prev().find('option:selected').val();

            ques_template += '<div style="margin-left: 20px;">';
            ques_template += '<select class="question-select" name="' + ((cd_name != '' || cd_name != undefined) ? cd_name : "") + '[qs_id]" data-index="' + next_hierarchy_index + '" data-posttype="' + URLs.PLUGIN_PREFIX + '-questions" required>';
            ques_template += '<option value="">Select question...</option>';

            for (let i = 0; i < ques_clone[0].length; i++) {
                const options = ques_clone[0][i];
                if (options.value != selected_val && options.value != "") {
                    ques_template += '<option value="' + options.value + '">';
                    ques_template += options.textContent;
                    ques_template += '</option>';
                }
            }

            ques_template += '</select>';
            ques_template += '</div>';
            _this.parent().append(ques_template);
        }
        else {
            $.ajax({
                type: 'post',
                cache: false,
                url: URLs.AJAX_URL,
                data: {
                    action: URLs.PLUGIN_PREFIX + "_condition_options",
                    value: value
                },
                success: function (res) {
                    res = JSON.parse(res);
                    let opt_template = '';

                    _this.next().remove();

                    if ((res.status === true || res.status === 1) && res.data != undefined) {
                        let data = res.data;
                        opt_template += '<div style="margin-left: 20px;">';
                        opt_template += '<select class="question-select" name="' + ((cd_name != '' || cd_name != undefined) ? cd_name : "") + '[page_id]" data-posttype="" required>';
                        opt_template += '<option value="">Select question...</option>'
                        for (const key in data) {
                            if (Object.hasOwnProperty.call(data, key)) {
                                const element = data[key];
                                opt_template += '<option value="' + element.id + '">';
                                opt_template += element.title;
                                opt_template += '</option>';
                            }
                        }
                        opt_template += '</select>';
                        opt_template += '</div>';
                        _this.parent().append(opt_template);
                    }
                    else {
                        if (value !== '')
                            alert(res.msg);
                        _this.find('>:first-child').prop('selected', true);
                    }
                }
            });
        }

    });

    /**
     * Entries JS 
     * 
     * */

    // Submission Table
    new DataTable('#quiz-entries');

    // View detail
    jQuery('.view-detail').on('click', function () {
        let _this = $(this);
        let data = JSON.parse(_this.parent().parent().find('.quiz_data').val());
        let tempalte = '';

        for (const key in data) {
            if (Object.hasOwnProperty.call(data, key)) {
                const element = data[key];
                tempalte += '<ul>';
                tempalte += '<span>' + element.question + '</span>';
                tempalte += '<li>' + element.option + '</li>';
                tempalte += '</ul>';
            }
        }
        $('#quizDetail .modal-body').empty().append(tempalte);
    });
});