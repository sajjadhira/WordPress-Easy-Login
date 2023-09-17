// use strict mode

"use strict";

setInterval(function () {
  fetch(urlFetech)
    .then((response) => response.json())
    .then((data) => {
      if (data.status == 1) {
        // set easyloginAdmin html to success message

        document.getElementById("easyloginAdmin").innerHTML =
          "<div class='success-message'><strong><span class='dashicons dashicons-saved'></span> Login Success</strong><br /><p>You are logged in successfully. You can close this window.</p></div>";
      } else if (data.status == 404) {
        // reload page
        window.location.reload();
      }
    });
}, 5000);

let text = document.getElementById("loginUrl").innerHTML;
const copyURL = async () => {
  try {
    await navigator.clipboard.writeText(text);
    // console.log("Content copied to clipboard");
    // chnage button text to copied
    document.getElementById("copyUrl").innerHTML =
      '<span class="dashicons dashicons-saved"></span> Link Copied to Clipboard';
    setTimeout(() => {
      document.getElementById("copyUrl").innerHTML =
        '<span class="dashicons dashicons-clipboard"></span> Copy Link';
    }, 5000);
  } catch (err) {
    console.error("Failed to copy: ", err);
  }
};
