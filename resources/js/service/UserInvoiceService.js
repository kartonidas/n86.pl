import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class UserInvoiceService {
    saleRegisterList(size, page) {
        var data = {
            size: size,
            page: page,
        };
        return axios.get('api/v1/sale-register', data);
    }
    
    saleRegisterCreate(data) {
        return axios.put('api/v1/sale-register', removeNullValues(data));
    }
    
    saleRegisterGet(id) {
        return axios.get('api/v1/sale-register/' + id);
    }
    
    saleRegisterUpdate(id, data) {
        return axios.put('api/v1/sale-register/' + id, removeNullValues(data));
    }
    
    saleRegisterRemove(id) {
        return axios.delete('api/v1/sale-register/' + id);
    }
}