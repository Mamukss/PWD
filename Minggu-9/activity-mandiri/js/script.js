function fn_ValForm() {
            var sMsg = "";
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

            if (document.getElementById("name").value == "") {
                sMsg += "\n* Anda belum mengisikan nama";
            }
                if (document.getElementById("email").value == "") {
                sMsg += "\n* Anda belum mengisikan email";
             } if(!emailPattern.test(document.getElementById("email").value)) {
                sMsg += "\n* Format email tidak valid";
            }
            if (document.getElementById("message").value == "") {
                sMsg += "\n* Anda belum mengisikan pesan";
            }

            if (sMsg != "") {
                alert("Peringatan:\n" + sMsg);
                return false;
            } else {
                return true;
            }
        }
    