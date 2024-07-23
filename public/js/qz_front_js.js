jQuery(document).ready(function ($) {
    // Function to handle click event on body buttons
    $(".body-btn").click(function () {
        let _this = $(this);
        let quiz_id = _this.data('quiz_id');
        let title = _this.attr('title');

        if (quiz_id != '') {
            jQuery.ajax({
                type: 'post',
                cache: false,
                url: URLs.AJAX_URL,
                data: {
                    action: "qz_quiz_render",
                    quiz_id: quiz_id
                },
                success: function (res) {
                    res = JSON.parse(res);

                    if (res.status === true) {
                        let data = res.data;
                        let template = '';

                        _this.addClass('active');
                        _this.nextAll('a').hide();
                        _this.prevAll('a').hide();
                        $('.experience-remove-content, .nextPrev-btns-main').hide();
                        $('#regForm .tab').remove();
                        $('.step-form').show();
                        $('.step-form>.body-part-head').text(title);
                        $('.user-form').show();

                        template += '<div class="tab" style="display: none;">';
                        template += '<label class="question">' + data.title + '</label>';
                        for (const key in data.options) {
                            if (Object.hasOwnProperty.call(data.options, key)) {
                                const element = data.options[key];
                                template += '<div class="qs-options">';
                                template += '<input type="radio" id="" name="options' + element.ques_id + '" value="" data-key="' + key + '" data-redirect="' + element.redirect + '">';
                                template += '<label>' + element.value + '</label>';
                                template += '</div>';
                            }
                        }
                        template += '</div>';

                        $(template).insertBefore($('.nextPrev-btns-main'));

                    }
                    else {
                        alertModal(res.msg, 'error', '');
                    }
                }
            })
        }
        else {
            alertModal('No quiz available', 'error', '');
        }


        // Hide all body buttons
        // $(".body-btn").hide();

        // Show only the clicked button
        // $(this).show();

        // Set class "experience-remove-content" to display none
        // $(".experience-remove-content").hide();

        // Set class "step-form" to display block
        // $(".step-form").show();
    });

    // Show tab after form
    $('#form_next').on('click', function () {
        let _this = $(this);
        let firstname = _this.parent().parent().find('#user_firstname');
        let lastname = _this.parent().parent().find('#user_lastname');
        let email = _this.parent().parent().find('#user_email');
        let phone = _this.parent().parent().find('#user_phone');
        let error_border = '1px solid #bf0000';
        let success_border = '1px solid #008000';
        let email_regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        let phon_regex = /^\d{10}$/;
        let check_obj = {
            firstname: false,
            lastname: false,
            email: false,
            phone: false
        };

        function allTrue(obj) {
            for (var o in obj) {
                if (!obj[o]) return false;
            }
            return true;
        }

        function hasWhiteSpace(s) {
            return s.indexOf(' ') >= 0;
        }

        function validatePhoneNumber(phoneNumber) {
            // Regular expression for US phone number format
            var regex = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;

            // Test the phone number against the regex
            return regex.test(phoneNumber);
        }

        // Firstname
        if (firstname.val() === '') {
            firstname.css('border', error_border);
            firstname.next().text('Please enter your firstname.');
            check_obj.firstname = false;
        }
        else if (hasWhiteSpace(firstname.val())) {
            firstname.css('border', error_border);
            firstname.next().text('Space not allowed in firstname.');
            check_obj.firstname = false;
        }
        else {
            firstname.css('border', success_border);
            firstname.next().text('');
            check_obj.firstname = true;
        }

        // Lastname
        if (lastname.val() === '') {
            lastname.css('border', error_border);
            lastname.next().text('Please enter your lastname.');
            check_obj.lastname = false;
        }
        else if (hasWhiteSpace(lastname.val())) {
            lastname.css('border', error_border);
            lastname.next().text('Space not allowed in lastname.');
            check_obj.lastname = false;
        }
        else {
            lastname.css('border', success_border);
            lastname.next().text('');
            check_obj.lastname = true;
        }

        // Email address
        if (email.val() === '') {
            email.css('border', error_border);
            email.next().text('Please enter your email address.');
            check_obj.email = false;
        }
        else if (!email_regex.test(email.val())) {
            email.css('border', error_border);
            email.next().text('Please enter a valid email address.');
            check_obj.email = false;
        }
        else {
            email.css('border', success_border);
            email.next().text('');
            check_obj.email = true;
        }

        // Phone number
        if (phone.val() === '') {
            phone.css('border', error_border);
            phone.next().text('Please enter your phone number.');
            check_obj.phone = false;
        }
        else if (!validatePhoneNumber(phone.val())) {
            phone.css('border', error_border);
            phone.next().text('Please enter a valid phone number (123-456-7890).');
            check_obj.phone = false;
        }
        else {
            phone.css('border', success_border);
            phone.next().text('');
            check_obj.phone = true;
        }

        if (allTrue(check_obj)) {
            _this.parent().parent().hide();
            $('#regForm .tab, .nextPrev-btns-main').show();
        }
    });


    // Next Button Click
    $('#nextBtn').on('click', function () {
        let _this = $(this);
        let key = _this.parents().eq(2).prev().find('input:checked').data('key');
        let redirect = _this.parents().eq(2).prev().find('input:checked').data('redirect');
        let quiz_id = $('.img-btns>a.active').data('quiz_id');

        if (redirect === 'empty') {
            $.ajax({
                type: 'post',
                cache: false,
                url: URLs.AJAX_URL,
                data: {
                    action: "qz_next_question",
                    quiz_id: quiz_id,
                    key: key
                },
                success: function (res) {
                    res = JSON.parse(res);
                    if (res.status === true) {
                        let data = res.data;
                        let template = '';

                        _this.parents().eq(2).prev().hide();

                        template += '<div class="tab" style="display: block;">';
                        template += '<label class="question">' + data.title + '</label>';
                        for (const key in data.options) {
                            if (Object.hasOwnProperty.call(data.options, key)) {
                                const element = data.options[key];
                                template += '<div class="qs-options">';
                                template += '<input type="radio" id="" name="options' + element.ques_id + '" value="" data-key="' + key + '" data-redirect="' + element.redirect + '">';
                                template += '<label>' + element.value + '</label>';
                                template += '</div>';
                            }
                        }
                        template += '</div>';

                        $(template).insertBefore(_this.parents().eq(2));
                        _this.addClass('btn_disabled');
                    } else {
                        alertModal(res.msg, 'error', '');
                    }
                }
            });
        }
        else {

            // Get data of questions
            let body_part = _this.parents().eq(4).find('.body-part-head').text();
            let firstname = _this.parents().eq(3).find('#user_firstname').val();
            let lastname = _this.parents().eq(3).find('#user_lastname').val();
            let email = _this.parents().eq(3).find('#user_email').val();
            let phone = _this.parents().eq(3).find('#user_phone').val();
            let ques_data = {
                body_part: body_part,
                firstname: firstname,
                lastname: lastname,
                email: email,
                phone: phone,
                list: []
            };
            _this.parents().eq(3).find('.tab').each(function (i, v) {
                let question_name = $(v).find('.question').text();
                let option_name = $(v).find('input:checked').next('label').text();
                ques_data.list.push({
                    question: question_name,
                    option: option_name
                });
            });

            // Save quiz data in database
            $.ajax({
                type: 'post',
                cache: false,
                url: URLs.AJAX_URL,
                data: {
                    action: "qz_save_quiz",
                    ques_data: ques_data
                },
                success: function (res) {
                    res = JSON.parse(res);
                }

            })

            alertModal('Thankyou', 'success', redirect);
        }
    });


    // Options click
    $(document).on('click', '.qs-options>input', function () {
        let _this = $(this);
        let redirect = _this.data('redirect');

        $('#nextBtn').removeClass('btn_disabled');
        if (redirect !== 'empty') {
            $('#nextBtn').addClass('save_form').text('Submit');
        }
        else {
            $('#nextBtn').removeClass('save_form').text('Next');
        }
    });
});

/* var currentTab = 0;
showTab(currentTab);

function showTab(n) {

    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";

    if (n == 0) {
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == x.length - 1) {
        document.getElementById("nextBtn").innerHTML = "Submit";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
    }

    fixStepIndicator(n);
}

function nextPrev(n) {

    var x = document.getElementsByClassName("tab");

    if (n == 1 && !validateForm()) return false;

    x[currentTab].style.display = "none";

    currentTab = currentTab + n;

    if (currentTab >= x.length) {

        document.getElementById("regForm").submit();
        return false;
    }

    showTab(currentTab);
}

function validateForm() {

    var x,
        y,
        i,
        valid = true;
    x = document.getElementsByClassName("tab");
    y = x[currentTab].getElementsByTagName("input");

    for (i = 0; i < y.length; i++) {

        if (y[i].value == "") {

            y[i].className += " invalid";

            valid = false;
        }
    }

    if (valid) {
        document.getElementsByClassName("step")[currentTab].className += " finish";
    }
    return valid;
}

function fixStepIndicator(n) {

    var i,
        x = document.getElementsByClassName("step");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" active", "");
    }

    x[n].className += " active";
} */

function alertModal(title, icon, redirect) {

    if (redirect != '') {
        Swal.fire({
            title: title,
            icon: icon
        }).then(() => {
            window.location.href = redirect;
        });
    }
    else {
        Swal.fire({
            title: title,
            icon: icon
        });
    }
}