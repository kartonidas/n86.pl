import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DashboardService {
    get() {
        return axios.get('api/v1/dashboard');
    }
}