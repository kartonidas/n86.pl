import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class CustomerService {
    list(size, page, sort, order) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
        };
        return axios.get('api/v1/customers', { params : data });
    }
    
    create(name, street, house_no, apartment_no, city, zip, nip) {
        var customerData = {
            name : name,
            street : street,
            house_no : house_no,
            apartment_no : apartment_no,
            city : city,
            zip : zip,
            nip : nip,
        };
        return axios.put('api/v1/customer', customerData);
    }
    
    get(customerId) {
        return axios.get('api/v1/customer/' + customerId);
    }
    
    update(customerId, name, street, house_no, apartment_no, city, zip, nip) {
        var customerData = {
            name : name,
            street : street,
            house_no : house_no,
            apartment_no : apartment_no,
            city : city,
            zip : zip,
            nip : nip,
        };
        
        return axios.put('api/v1/customer/' + customerId, removeNullValues(customerData));
    }
    
    remove(customerId) {
        return axios.delete('api/v1/customer/' + customerId);
    }
}