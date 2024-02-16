import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class HistoryService {
    list(objectType, objectId, meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/history/' + objectType + '/' + objectId, { params : removeNullValues(data) });
    }
}