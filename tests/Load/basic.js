import http from 'k6/http';
import { sleep } from 'k6';

export let options = {
  stages: [
    { duration: '30s', target: 10 },
    { duration: '1m30s', target: 20 },
    { duration: '30s', target: 0 },
  ],
};

export default function () {
  http.get('https://mbizon.eastus.cloudapp.azure.com:8000');
  sleep(1);
}
