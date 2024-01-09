import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ConfigService {
    get() {
        return axios.get('api/v1/config');
    }
    
    update(configData) {
        return axios.put('api/v1/config', configData);
    }
}