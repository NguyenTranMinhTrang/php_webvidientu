
var BASE_URL = 'http://localhost/cuoiki/server';
var API_AUTHEN = '/api/authen.php';

function xacminh(id) {

    let data = {
        'action': 'xacminh',
        'id': id
    };

    $.ajax({
        url: BASE_URL + API_AUTHEN,
        type: "POST",
        data: data,
        dataType: "json",
        success: function (response) {
            const data = JSON.stringify(JSON.parse(response));
            if (data) {
                if (data['code'] == 0) {
                    alert(data['error']);
                }
            }
            location.reload();
        }

    });
}