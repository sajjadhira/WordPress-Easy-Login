"use strict";
let urlFetech = document.getElementById("fetechUrl").innerHTML;
let urlCopy = document.getElementById("loginUrl").innerHTML;

setInterval(function () {
  fetch(urlFetech)
    .then((response) => response.json())
    .then((data) => {
      if (data.status == 1) {
        // get redirect url
        window.location.href = data.redirect;
      } else if (data.status == 404) {
        // reload page
        // window.location.reload();
        let newURL = data.url;
        let imgURL = data.qr;

        // change image url

        document.getElementById("imageURL").src = imgURL;

        // chnage loginUrl to new url

        document.getElementById("loginUrl").innerHTML = newURL;
        urlCopy = newURL;

        // set new fetech url

        document.getElementById("fetechUrl").innerHTML = data.fetechUrl;
        urlFetech = data.fetechUrl;
      }
    });
}, 3000);

const EasyLogincopyURL = async () => {
  try {
    await navigator.clipboard.writeText(urlCopy);
    // console.log('Content copied to clipboard');

    // change text of copyToUrl id

    document.getElementById("copyToUrl").innerHTML = copiedText;

    setTimeout(() => {
      // change text of copyToUrl id

      document.getElementById("copyToUrl").innerHTML = copyText;
    }, 5000);
  } catch (err) {
    console.error("Failed to copy: ", err);
  }
};

// onclick copyToUrl id call EasyLogincopyURL function

document.getElementById("copyToUrl").onclick = EasyLogincopyURL;
