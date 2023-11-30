import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';
import { useI18n } from 'vue-i18n';

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
    
    types() {
        const { t } = useI18n();
        return [
            {"id" : "person", "name" : t('customers.type_person')},
            {"id" : "firm", "name" : t('customers.type_firm')},
        ];
    }
}