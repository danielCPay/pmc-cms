/*
 * Creator: Firefox 90.0
 * Browser: Firefox 90.0
 */

import { sleep, group } from "k6";
import http from "k6/http";

export const options = {
  thresholds: {
    // 90% of requests must finish within 400ms.
    http_req_duration: ['p(90) < 1000', 'p(95) < 500'],
    http_req_failed: ['rate<0.01'],
  },
  throw: true,
  vus: 10,
  duration: "30s"
};

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
  let csrf;

  const readCsrf = (script) => {
    const match = /([a-z0-9]{40},\d{10})/.exec(script);
    if (match !== null) {
      csrf = 'sid:' + match[0];
    }
  }

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        {
          _csrf: csrf,
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
          _csrf: csrf,
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
          _csrf: csrf,
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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

      response = http.post(
        "https://mbizon.eastus.cloudapp.azure.com:8000/index.php",
        "_csrf=" + encodeURIComponent(csrf) + "&action=ChangesReviewedOn&mode=getUnreviewed&module=ModTracker&sourceModule=ClaimOpportunities&recordsId%5B%5D=14691&recordsId%5B%5D=14791&recordsId%5B%5D=14830&recordsId%5B%5D=15420&recordsId%5B%5D=15627&recordsId%5B%5D=15669&recordsId%5B%5D=15905&recordsId%5B%5D=16111&recordsId%5B%5D=16117&recordsId%5B%5D=16649&recordsId%5B%5D=16669&recordsId%5B%5D=16689&recordsId%5B%5D=16709&recordsId%5B%5D=21949&recordsId%5B%5D=21970&recordsId%5B%5D=22010&recordsId%5B%5D=22062&recordsId%5B%5D=22391&recordsId%5B%5D=23072&recordsId%5B%5D=23274",
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
          _csrf: csrf,
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
          _csrf: csrf,
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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());

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
      readCsrf(response.html().find('script').filter((_idx, el) => el.text().startsWith('var csrfMagicToken')).text());
    }
  );

  // Automatically added sleep
  sleep(1);
}
