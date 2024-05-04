var pageID = 1;
var chatbox = "../index/chat/list",
    loadcontent =
        '<li class="list-group-item">Đang tải dữ liệu <i class="fa fa-spin fa-hourglass-half"></i></li>';
$(document).ready(function () {
    $("#idChat").html(loadcontent),
        $.get(chatbox, function (t) {
            $("#idChat").html(t).hide().slideDown("slow");
        });
    var a = $("#form"),
        e = $("#submit"),
        i = $("#alert"),
        n = $("#postText");
    a.on("submit", function (t) {
        return (
            t.preventDefault(),
            "" == n
                ? (i.show(),
                    i.text("Bạn chưa nhập nội dung !!!"),
                    $("#postText").focus(),
                    !1)
                : void $.ajax({
                    url: "../index/chat/send",
                    type: "POST",
                    timeout: 5e3,
                    dataType: "html",
                    data: a.serialize(),
                    beforeSend: function () {
                        i.fadeOut(),
                            e.html('Đang gửi <i class="fa fa-spinner fa-spin fa-fw"></i>');
                    },
                    success: function (t) {
                        a.trigger("reset"),
                            $("#postText").focus(),
                            $("#postText").val(""),
                            e.html('<i class="fa fa-check" aria-hidden="true"></i> Chat');
                    },
                    error: function (t) {
                        console.log(t);
                    },
                })
        );
    });
});

function phanTrangChat(totalChat, pageID) {
    const itemsPerPage = 10;
    const totalPage = Math.ceil(totalChat / itemsPerPage);

    console.log(`Tong chat ${totalChat}`);
    console.log(`Tong page ${totalPage}`);

    $("#phan-trang").empty();

    const renderPageLink = (pageNumber) => {
        $("#phan-trang").append(
            `<li class="page-item">
                <a class="page-link" onclick="loadTrang(${totalChat}, ${pageNumber})">${pageNumber}</a>
            </li>`
        );
    };

    if (pageID > 1) {
        $("#phan-trang").append(
            `<li class="page-item">
                <a class="page-link" aria-label="Previous" onclick="loadTrang(${totalChat}, ${pageID - 1})">
                    <span aria-hidden="true">«</span>
                </a>
            </li>`
        );
    }

    if (pageID > 3) {
        renderPageLink(1);
    }

    if (pageID > 4) {
        $("#phan-trang").append('<li class="page-item"><a class="page-link">...</a></li>');
    }

    if (pageID > 2) {
        renderPageLink(pageID - 2);
    }

    if (pageID > 1) {
        renderPageLink(pageID - 1);
    }

    $("#phan-trang").append(`<li class="page-item active"><b class="page-link">${pageID}</b></li>`);

    if (pageID < totalPage - 1) {
        renderPageLink(pageID + 1);
    }

    if (pageID < totalPage - 2) {
        renderPageLink(pageID + 2);
    }

    if (pageID < totalPage - 3) {
        $("#phan-trang").append('<li class="page-item"><a class="page-link">...</a></li>');
    }

    if (pageID < totalPage) {
        renderPageLink(totalPage);
    }

    if (pageID < totalPage) {
        $("#phan-trang").append(
            `<li class="page-item">
                <a class="page-link" onclick="loadTrang(${totalChat}, ${pageID + 1})">»</a>
            </li>`
        );
    }
}

async function gogoChat() {
    reload_chat = setInterval(async function () {
        fetch("/index/chat/count")
            .then((t) => t.json())
            .then((t) => {
                for (var a = t; a > totalChat;) {
                    totalChat++;
                    var m = "../chat/ele?chatID=" + totalChat;
                    $.get(m, function (t) {
                        $("#idChat").prepend(t),
                            $("#idChat .list-group-item:last").remove(),
                            phanTrangChat(totalChat, pageID);
                    });
                }
            });
    }, 2e3);
}

function loadTrang(totalChat, pageID) {
    $("#idChat").empty();

    var chatli = "../index/chat/list?page=" + pageID;
    $.get(chatli, function (t) {
        $("#idChat").append(t);
    });

    document.getElementById("chat-place").scrollIntoView();
    phanTrangChat(totalChat, pageID);
}

gogoChat();
phanTrangChat(totalChat, pageID);