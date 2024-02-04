import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class FaulrService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/faults', { params : data });
    }
    
    create(faultData) {
        return axios.put('api/v1/fault', removeNullValues(faultData));
    }
    
    get(faultId) {
        return axios.get('api/v1/fault/' + faultId);
    }
    
    update(faultId, faultData) {
        return axios.put('api/v1/fault/' + faultId, removeNullValues(faultData));
    }
    
    remove(faultId) {
        return axios.delete('api/v1/fault/' + faultId);
    }
}