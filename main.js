
var BASE_URL = 'http://localhost/cuoiki/server';
var API_AUTHEN = '/api/authen.php';
var API_ADMIN = '/api/admin';
var API_USER = '/api/user';

$(function () {
    $('#forgetPassword').submit(function (e) {
        e.preventDefault();
        var email = $('[name=email]').val();
        var phone = $('[name=phone]').val();

        let data = {
            email: email,
            phone: phone
        };

        $.ajax({
            url: BASE_URL + API_USER + '/getotp.php',
            type: "POST",
            data: JSON.stringify(data),
            dataType: 'json',
            contentType: 'application/json',
            success: function (response) {
                const data = JSON.parse(JSON.stringify(response));
                console.log(data);
                if (data) {
                    if (data['code'] == 0) {
                        window.location = BASE_URL + '/verifyotp.php';
                    }
                    else {
                        alert(data['data']);
                    }
                }
            }

        });

    });
});

$(function () {
    $('#verifyOtp').submit(function (e) {
        e.preventDefault();
        var email = $('[name=email]').val();
        var otp = $('[name=otp]').val();

        let data = {
            email: email,
            otp_code: otp
        };

        $.ajax({
            url: BASE_URL + API_USER + '/verifyotp.php',
            type: "POST",
            data: JSON.stringify(data),
            dataType: 'json',
            contentType: 'application/json',
            success: function (response) {
                const data = JSON.parse(JSON.stringify(response));
                console.log(data);
                if (data) {
                    if (data['code'] == 0) {
                        window.location = BASE_URL + '/changePassword.php';
                    }
                    else {
                        alert(data['data']);
                    }
                }
            }

        });

    });
});

function xacminh(id) {

    let data = {
        id: id
    };

    $.ajax({
        url: BASE_URL + API_ADMIN + '/xacminh.php',
        type: "POST",
        data: JSON.stringify(data),
        dataType: 'json',
        contentType: 'application/json',
        success: function (response) {
            const data = JSON.parse(JSON.stringify(response));
            if (data) {
                if (data['code'] == 0) {
                    alert(data['data']);
                }
                else {
                    location.reload();
                }
            }
        }

    });
}

function huy(id) {

    let data = {
        id: id
    };

    $.ajax({
        url: BASE_URL + API_ADMIN + '/huy.php',
        type: "POST",
        data: JSON.stringify(data),
        dataType: 'json',
        contentType: 'application/json',
        success: function (response) {
            const data = JSON.parse(JSON.stringify(response));
            if (data) {
                if (data['code'] == 0) {
                    alert(data['data']);
                }
                else {
                    alert('Đã vô hiệu hóa user!');
                }
            }
        }

    });
}


function bosung(id) {

    let data = {
        id: id
    };

    $.ajax({
        url: BASE_URL + API_ADMIN + '/bosung.php',
        type: "POST",
        data: JSON.stringify(data),
        dataType: 'json',
        contentType: 'application/json',
        success: function (response) {
            const data = JSON.parse(JSON.stringify(response));
            if (data) {
                if (data['code'] == 0) {
                    alert(data['data']);
                }
                else {
                    alert('Yêu cầu thông tin user!');
                }
            }
        }

    });
}

function mokhoa(id) {

    let data = {
        id: id
    };

    $.ajax({
        url: BASE_URL + API_ADMIN + '/mokhoa.php',
        type: "POST",
        data: JSON.stringify(data),
        dataType: 'json',
        contentType: 'application/json',
        success: function (response) {
            const data = JSON.parse(JSON.stringify(response));
            if (data) {
                if (data['code'] == 0) {
                    alert(data['data']);
                }
                else {
                    alert('Mở khóa thành công user!');
                    location.reload();
                }
            }
        }

    });
}

