import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class CustomerService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search,
        };
        return axios.get('api/v1/customers', { params : data });
    }
    
    create(customerData) {
        return axios.put('api/v1/customer', customerData);
    }
    
    get(customerId) {
        return axios.get('api/v1/customer/' + customerId);
    }
    
    update(customerId, customerData) {
        return axios.put('api/v1/customer/' + customerId, removeNullValues(customerData));
    }
    
    remove(customerId) {
        return axios.delete('api/v1/customer/' + customerId);
    }
}