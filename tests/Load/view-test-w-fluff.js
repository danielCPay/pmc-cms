/*
 * Creator: Firefox 90.0
 * Browser: Firefox 90.0
 */

import { sleep, group } from "k6";
import http from "k6/http";

export const options = {};

export default function main() {
  const commonHeaders = {
    Host: "mbizon.eastus.cloudapp.azure.com:8000",
    "User-Agent":
      "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
    Accept: "text/css,*/*;q=0.1",
    "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
    "Accept-Encoding": "gzip, deflate, br",
    DNT: "1",
    Connection: "keep-alive",
    // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
    "Sec-Fetch-Dest": "style",
    "Sec-Fetch-Mode": "no-cors",
    "Sec-Fetch-Site": "same-origin",
    TE: "trailers",
  };
  const cos = [26635, 16117, 26594, 26525, 26440, 26080, 25502, 25478, 24024, 24001, 23660, 23159, 23072, 15803, 15537, 15284, 15215, 14549];
  const coId = cos[Math.floor(Math.random() * cos.length)];

  let response;

  group(
    "page_1 - Potencjalne roszczenia Faktura zbiorcza CLSwFA Tomaszewski 3",
    function () {
      response = http.get("https://mbizon.eastus.cloudapp.azure.com:8000/", {
        headers: {
          Host: "mbizon.eastus.cloudapp.azure.com:8000",
          "User-Agent":
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
          Accept:
            "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
          "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
          "Accept-Encoding": "gzip, deflate, br",
          DNT: "1",
          Connection: "keep-alive",
          "Upgrade-Insecure-Requests": "1",
          "Sec-Fetch-Dest": "document",
          "Sec-Fetch-Mode": "navigate",
          "Sec-Fetch-Site": "none",
          "Sec-Fetch-User": "?1",
        },
      });

      function logCookie(c) {
        // Here we log the name and value of the cookie along with additional attributes.
        // For full list of attributes see:
        // https://k6.io/docs/using-k6/cookies#properties-of-a-response-cookie-object
        const output = `
           ${c.name}: ${c.value}
           \tdomain: ${c.domain}
           \tpath: ${c.path}
           \texpires: ${c.expires}
           \thttpOnly: ${c.http_only}
        `;
      
        console.log(output);
      }

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/adminIcon.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/additionalIcons.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/yfm.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/yfi.css?s=1617299272",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@mdi/font/css/materialdesignicons.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@fortawesome/fontawesome-free/css/all.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/core/dist/PNotify.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/confirm/dist/PNotifyConfirm.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/mobile/dist/PNotifyMobile.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/desktop/dist/PNotifyDesktop.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-dist/jquery-ui.min.css?s=1626276791",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/select2/dist/css/select2.min.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/perfect-scrollbar/css/perfect-scrollbar.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/css/validationEngine.jquery.css?s=1585650117",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-tabdrop/css/tabdrop.css?s=1508934818",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css?s=1626276784",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-daterangepicker/daterangepicker.css?s=1626276784",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/footable/css/footable.core.min.css?s=1626276789",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/clockpicker/dist/bootstrap4-clockpicker.min.css?s=1585642454",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/animate.css/animate.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/tributejs/dist/tribute.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emojipanel/dist/emojipanel.css?s=1584205870",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emoji-mart-vue-fast/css/emoji-mart.css?s=1626276786",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/overlayscrollbars/css/OverlayScrollbars.min.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/src/css/quasar.css?s=1615976681",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/calendar.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/owners.css?s=1623866751",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/modules.css?s=1624441815",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/picklists.css?s=1626181831",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/fields.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/styleTemplate.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/styles/Main.min.css?s=1619510469",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/skins/twilight/style.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Users/Login.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery/dist/jquery.min.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/device-uuid/lib/device-uuid.min.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Users/resources/Login.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/adminIcon.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Logo/logo",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/additionalIcons.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/yfm.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/yfi.css?s=1617299272",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@mdi/font/css/materialdesignicons.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@fortawesome/fontawesome-free/css/all.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/core/dist/PNotify.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/confirm/dist/PNotifyConfirm.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/mobile/dist/PNotifyMobile.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/desktop/dist/PNotifyDesktop.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-dist/jquery-ui.min.css?s=1626276791",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/select2/dist/css/select2.min.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/perfect-scrollbar/css/perfect-scrollbar.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/css/validationEngine.jquery.css?s=1585650117",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-tabdrop/css/tabdrop.css?s=1508934818",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css?s=1626276784",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-daterangepicker/daterangepicker.css?s=1626276784",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/footable/css/footable.core.min.css?s=1626276789",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/clockpicker/dist/bootstrap4-clockpicker.min.css?s=1585642454",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/animate.css/animate.min.css?s=1626276783",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/tributejs/dist/tribute.css?s=1626276793",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emojipanel/dist/emojipanel.css?s=1584205870",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emoji-mart-vue-fast/css/emoji-mart.css?s=1626276786",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/overlayscrollbars/css/OverlayScrollbars.min.css?s=1626276792",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/src/css/quasar.css?s=1615976681",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/fields.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/calendar.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/owners.css?s=1623866751",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/modules.css?s=1624441815",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/colors/picklists.css?s=1626181831",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/styleTemplate.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/styles/Main.min.css?s=1619510469",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/skins/twilight/style.min.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Users/Login.css?s=1614859016",
        {
          headers: commonHeaders,
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "application/font-woff2;q=1.0,application/font-woff;q=0.9,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "identity",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "font",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/images/favicon.ico",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php?module=Users&action=Login",
        {
          username: "adminmk",
          password: "Majkel13",
          loginLanguage: "pl-PL",
          fingerprint: "d520c7a8-421b-4563-b955-f5abc56b97ec",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded",
            Origin: "null",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=koe82v4bhg385hh77mcj53dbrl",
            "Upgrade-Insecure-Requests": "1",
            "Sec-Fetch-Dest": "document",
            "Sec-Fetch-Mode": "navigate",
            "Sec-Fetch-Site": "same-origin",
            "Sec-Fetch-User": "?1",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Upgrade-Insecure-Requests": "1",
            "Sec-Fetch-Dest": "document",
            "Sec-Fetch-Mode": "navigate",
            "Sec-Fetch-Site": "none",
            "Sec-Fetch-User": "?1",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/gridstack/dist/gridstack.min.css?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/fullcalendar/dist/fullcalendar.min.css?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery/dist/jquery.min.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/yetiforce/csrf-magic/src/Csrf.min.js",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=MultiCompany&action=Logo&record=1&key=11111111111111111111111111111111111111111111111111",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=Users&action=MultiImage&field=imagename&record=1&key=246402b90fe4ec761b9d0124716cd5f4ad04727595CsgU9wuJ",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Logo/logo_hor.png",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/block-ui/jquery.blockUI.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/select2/dist/js/select2.full.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-dist/jquery-ui.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery.class.js/jquery.class.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/perfect-scrollbar/dist/perfect-scrollbar.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-slimscroll/jquery.slimscroll.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/core/dist/PNotify.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/mobile/dist/PNotifyMobile.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/desktop/dist/PNotifyDesktop.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/confirm/dist/PNotifyConfirm.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/font-awesome5/dist/PNotifyFontAwesome5.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-hoverintent/jquery.hoverIntent.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/popper.js/dist/umd/popper.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js?s=1508934818",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootbox/dist/bootbox.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/jquery.validationEngine.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/moment/min/moment.min.js?s=1626276785",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-daterangepicker/daterangepicker.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-outside-events/jquery.ba-outside-events.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/footable/dist/footable.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/ckeditor.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/adapters/jquery.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/tributejs/dist/tribute.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emojipanel/dist/emojipanel.js?s=1584205870",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/vue/dist/vue.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/libraries/quasar.config.js?s=1614859016",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/quasar.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/icon-set/mdi-v3.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/blueimp-file-upload/js/jquery.fileupload.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/floatthead/dist/jquery.floatThead.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/store/dist/store.legacy.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/clockpicker/dist/bootstrap4-clockpicker.min.js?s=1585642454",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/inputmask/dist/jquery.inputmask.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/mousetrap/mousetrap.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/html2canvas/dist/html2canvas.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/overlayscrollbars/js/jquery.overlayScrollbars.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/app.min.js?s=1625223326",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/fields/MultiImage.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Fields.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Tools.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/helper.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Connector.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/ProgressIndicator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-pl.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Menu.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Header.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Edit.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Field.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/BaseValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/FieldValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/BasicSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/ConditionBuilder.min.js?s=1625223316",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceFilter.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/OSSMail/resources/checkmails.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/views/KnowledgeBase/KnowledgeBase.vue.js?s=1619457362",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Vtiger.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/DashBoard.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/gridstack/dist/gridstack.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/gridstack/dist/gridstack.jQueryUI.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/css-element-queries/src/ResizeSensor.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/css-element-queries/src/ElementQueries.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/chart.js/dist/Chart.min.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/chartjs-plugin-funnel/dist/chart.funnel.min.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-lazy/jquery.lazy.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/dashboards/Widget.min.js?s=1625223316",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/fullcalendar/dist/fullcalendar.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap/dist/js/bootstrap.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/fonts/yfm.ttf?my5wef",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "application/font-woff2;q=1.0,application/font-woff;q=0.9,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "font",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/icons/fonts/yfi.ttf?93a0ly",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "application/font-woff2;q=1.0,application/font-woff;q=0.9,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "font",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@fortawesome/fontawesome-free/webfonts/fa-brands-400.woff2",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "application/font-woff2;q=1.0,application/font-woff;q=0.9,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "identity",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "font",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@mdi/font/fonts/materialdesignicons-webfont.woff2?v=5.9.55",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "application/font-woff2;q=1.0,application/font-woff;q=0.9,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "identity",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "font",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:597c9b86143a4d0afef380ab7e04e24d0540b862,1626361376",
          view: "BasicAjax",
          mode: "getDashBoardPredefinedWidgets",
          module: "Home",
          dashboardId: "1",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:597c9b86143a4d0afef380ab7e04e24d0540b862,1626361376",
          module: "Calendar",
          view: "Reminders",
          type_remainder: "true",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:597c9b86143a4d0afef380ab7e04e24d0540b862,1626361376",
          module: "Notification",
          view: "Reminders",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/sounds/sound_1.mp3",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "audio/webm,audio/ogg,audio/wav,audio/*;q=0.9,application/ogg;q=0.7,video/*;q=0.6,*/*;q=0.5",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            Range: "bytes=0-",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "audio",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/sounds/sound_1.mp3",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "audio/webm,audio/ogg,audio/wav,audio/*;q=0.9,application/ogg;q=0.7,video/*;q=0.6,*/*;q=0.5",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            Range: "bytes=0-",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "audio",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/images/favicon.ico",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php?module=ClaimOpportunities&view=List&mid=213&parent=215",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Upgrade-Insecure-Requests": "1",
            "Sec-Fetch-Dest": "document",
            "Sec-Fetch-Mode": "navigate",
            "Sec-Fetch-Site": "same-origin",
            "Sec-Fetch-User": "?1",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery/dist/jquery.min.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/yetiforce/csrf-magic/src/Csrf.min.js",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/images/loading.gif",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/block-ui/jquery.blockUI.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/select2/dist/js/select2.full.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-dist/jquery-ui.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery.class.js/jquery.class.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/perfect-scrollbar/dist/perfect-scrollbar.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-slimscroll/jquery.slimscroll.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/core/dist/PNotify.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/mobile/dist/PNotifyMobile.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/desktop/dist/PNotifyDesktop.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/confirm/dist/PNotifyConfirm.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/font-awesome5/dist/PNotifyFontAwesome5.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-hoverintent/jquery.hoverIntent.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/popper.js/dist/umd/popper.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap/dist/js/bootstrap.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js?s=1508934818",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootbox/dist/bootbox.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/jquery.validationEngine.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/moment/min/moment.min.js?s=1626276785",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-daterangepicker/daterangepicker.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-outside-events/jquery.ba-outside-events.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/footable/dist/footable.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/ckeditor.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/adapters/jquery.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/tributejs/dist/tribute.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emojipanel/dist/emojipanel.js?s=1584205870",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/vue/dist/vue.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/libraries/quasar.config.js?s=1614859016",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/quasar.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/icon-set/mdi-v3.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/blueimp-file-upload/js/jquery.fileupload.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/floatthead/dist/jquery.floatThead.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/store/dist/store.legacy.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/clockpicker/dist/bootstrap4-clockpicker.min.js?s=1585642454",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/inputmask/dist/jquery.inputmask.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/mousetrap/mousetrap.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/html2canvas/dist/html2canvas.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/overlayscrollbars/js/jquery.overlayScrollbars.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/app.min.js?s=1625223326",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/fields/MultiImage.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Fields.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Tools.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/helper.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Connector.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/ProgressIndicator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-pl.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Menu.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Header.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Edit.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Field.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/BaseValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/FieldValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/BasicSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/ConditionBuilder.min.js?s=1625223316",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceFilter.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/OSSMail/resources/checkmails.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/views/KnowledgeBase/KnowledgeBase.vue.js?s=1619457362",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Vtiger.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/List.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/CustomView/resources/CustomView.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/ListSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=MultiCompany&action=Logo&record=1&key=11111111111111111111111111111111111111111111111111",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=Users&action=MultiImage&field=imagename&record=1&key=246402b90fe4ec761b9d0124716cd5f4ad04727595CsgU9wuJ",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/images/favicon.ico",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        "_csrf=sid%3A4712aef0bdbc1ce290ebd660020a48f468005955%2C1626361386&action=ChangesReviewedOn&mode=getUnreviewed&module=ModTracker&sourceModule=ClaimOpportunities&recordsId%5B%5D=14691%2C14791%2C14830%2C15420%2C15627%2C15669%2C15905%2C16111%2C16117%2C16649%2C16669%2C16689%2C16709%2C21949%2C21970%2C22010%2C22062%2C22391%2C23072%2C23274",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "application/json, text/javascript, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:4712aef0bdbc1ce290ebd660020a48f468005955,1626361386",
          module: "Calendar",
          view: "Reminders",
          type_remainder: "true",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:4712aef0bdbc1ce290ebd660020a48f468005955,1626361386",
          module: "Notification",
          view: "Reminders",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php?module=ClaimOpportunities&view=Detail&record=" + coId + "&parent=215&mid=213",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php?module=ClaimOpportunities&view=Detail&record=" + coId + "&parent=215&mid=213",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept:
              "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Upgrade-Insecure-Requests": "1",
            "Sec-Fetch-Dest": "document",
            "Sec-Fetch-Mode": "navigate",
            "Sec-Fetch-Site": "same-origin",
            "Sec-Fetch-User": "?1",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet/dist/leaflet.css?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet.markercluster/dist/MarkerCluster.Default.css?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet.markercluster/dist/MarkerCluster.css?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.css?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery/dist/jquery.min.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/yetiforce/csrf-magic/src/Csrf.min.js",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/block-ui/jquery.blockUI.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/select2/dist/js/select2.full.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-ui-dist/jquery-ui.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery.class.js/jquery.class.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/perfect-scrollbar/dist/perfect-scrollbar.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-slimscroll/jquery.slimscroll.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/core/dist/PNotify.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/mobile/dist/PNotifyMobile.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/desktop/dist/PNotifyDesktop.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/confirm/dist/PNotifyConfirm.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/@pnotify/font-awesome5/dist/PNotifyFontAwesome5.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-hoverintent/jquery.hoverIntent.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/popper.js/dist/umd/popper.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap/dist/js/bootstrap.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js?s=1508934818",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootbox/dist/bootbox.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/jquery.validationEngine.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/moment/min/moment.min.js?s=1626276785",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/bootstrap-daterangepicker/daterangepicker.js?s=1626276784",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jquery-outside-events/jquery.ba-outside-events.min.js?s=1626276791",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/footable/dist/footable.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/ckeditor.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/adapters/jquery.js?s=1604931663",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/tributejs/dist/tribute.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/emojipanel/dist/emojipanel.js?s=1584205870",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/vue/dist/vue.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/libraries/quasar.config.js?s=1614859016",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/quasar.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/quasar/dist/icon-set/mdi-v3.umd.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/blueimp-file-upload/js/jquery.fileupload.js?s=1626276783",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/floatthead/dist/jquery.floatThead.min.js?s=1626276789",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/store/dist/store.legacy.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/clockpicker/dist/bootstrap4-clockpicker.min.js?s=1585642454",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/inputmask/dist/jquery.inputmask.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/mousetrap/mousetrap.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/html2canvas/dist/html2canvas.min.js?s=1626276790",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/overlayscrollbars/js/jquery.overlayScrollbars.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/app.min.js?s=1625223326",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/fields/MultiImage.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Fields.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Tools.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/helper.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Connector.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/ProgressIndicator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-pl.min.js?s=1585650117",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Menu.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Header.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Edit.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/Field.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/BaseValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/validator/FieldValidator.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/BasicSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/ConditionBuilder.min.js?s=1625223316",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceFilter.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/AdvanceSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/OSSMail/resources/checkmails.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/resources/views/KnowledgeBase/KnowledgeBase.vue.js?s=1619457362",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Vtiger.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/Detail.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/split.js/dist/split.min.js?s=1626276793",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/List.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/ListSearch.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/RelatedList.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet/dist/leaflet.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet.markercluster/dist/leaflet.markercluster.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.min.js?s=1626276792",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/OpenStreetMap/resources/Map.min.js?s=1625223313",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/modules/Vtiger/resources/dashboards/Widget.min.js?s=1625223316",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/libraries/chart.js/dist/Chart.min.js?s=1626276786",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=MultiCompany&action=Logo&record=1&key=11111111111111111111111111111111111111111111111111",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/file.php?module=Users&action=MultiImage&field=imagename&record=1&key=246402b90fe4ec761b9d0124716cd5f4ad04727595CsgU9wuJ",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "ClaimOpportunities",
          view: "Detail",
          record: coId,
          mode: "showModuleDetailView",
          toWidget: "true",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/layouts/basic/images/favicon.ico",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "image/webp,*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "image",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "ClaimOpportunities",
          view: "Detail",
          record: coId,
          mode: "showRelatedRecords",
          relatedModule: "Documents",
          page: "1",
          limit: "5",
          viewType: "List",
          relationId: "807",
          no_result_text: "0",
          fields: "document_type,notes_title,acceptance_status",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        "_csrf=sid%3A25e0fb87f01da622570dc5267326c9b642d41f63%2C1626361393&action=ChangesReviewedOn&mode=getUnreviewed&module=ModTracker&sourceModule=Documents&recordsId%5B%5D=16140%2C16141%2C16145%2C16146%2C16147",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "application/json, text/javascript, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "ClaimOpportunities",
          view: "Detail",
          record: coId,
          mode: "showRecentActivities",
          page: "1",
          limit: "5",
          skipHeader: "true",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "ClaimOpportunities",
          action: "RelationAjax",
          record: coId,
          relatedModule: "ModTracker",
          mode: "getRelatedListPageCount",
          relationId: "",
          tab_label: "LBL_UPDATES",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "application/json, text/javascript, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        "_csrf=sid%3A25e0fb87f01da622570dc5267326c9b642d41f63%2C1626361393&action=ChangesReviewedOn&mode=getUnreviewed&module=ModTracker&sourceModule=Documents&recordsId%5B%5D=16140%2C16141%2C16145%2C16146%2C16147",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "application/json, text/javascript, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "Calendar",
          view: "Reminders",
          type_remainder: "true",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: "sid:25e0fb87f01da622570dc5267326c9b642d41f63,1626361393",
          module: "Notification",
          view: "Reminders",
        },
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/html, */*; q=0.01",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest",
            Origin: "https://mbizon.eastus.cloudapp.azure.com:8000",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/config.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/skins/moono-lisa/editor_gecko.css?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/styles.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/colorbutton/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/pagebreak/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/colordialog/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/find/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/selectall/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/showblocks/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/div/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/print/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/font/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/justify/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/bidi/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/ckeditor-image-to-base/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/emoji/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/mentions/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/panelbutton/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/autocomplete/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/textmatch/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/ajax/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/preview/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/textwatcher/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/xml/plugin.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/tableselection/styles/tableselection.css",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/wsc/skins/moono-lisa/wsc.css?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/autocomplete/skins/default.css",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/colorbutton/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/pagebreak/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/colordialog/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/find/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/selectall/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/showblocks/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/div/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/print/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/bidi/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/font/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/emoji/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/preview/lang/pl.js?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "script",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/emoji/skins/default.css",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/dialog/styles/dialog.css",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/plugins/emoji/emoji.json?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "*/*",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );

      response = http.get(
        "https://mbizon.eastus.cloudapp.azure.com:8000/vendor/ckeditor/ckeditor/contents.css?t=KA9B",
        {
          headers: {
            Host: "mbizon.eastus.cloudapp.azure.com:8000",
            "User-Agent":
              "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
            Accept: "text/css,*/*;q=0.1",
            "Accept-Language": "pl,en-GB;q=0.7,en;q=0.3",
            "Accept-Encoding": "gzip, deflate, br",
            DNT: "1",
            Connection: "keep-alive",
            Referer:
              "https://mbizon.eastus.cloudapp.azure.com:8000/index.php?module=ClaimOpportunities&view=Detail&record=" + coId + "&parent=215&mid=213",
            // Cookie: "YTSID=gmjo0naptsh9tes0rbl0bpv3e4",
            "Sec-Fetch-Dest": "style",
            "Sec-Fetch-Mode": "no-cors",
            "Sec-Fetch-Site": "same-origin",
            TE: "trailers",
          },
        }
      );
    }
  );

  // Automatically added sleep
  sleep(1);
}
