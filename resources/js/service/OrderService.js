import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class OrderService {
    order(orderData) {
        return axios.post('api/v1/order/create', orderData);
    }
    
    prolong(orderData) {
        return axios.post('api/v1/order/prolong', orderData);
    }
    
    extend(orderData) {
        return axios.post('api/v1/order/extend', orderData);
    }
    
    getActiveSubscription() {
        return axios.get('api/v1/subscription');
    }
    
    validateInvoicingData() {
        return axios.get('api/v1/validate-invoicing-data');
    }
    
    invoices(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/invoices', { params : removeNullValues(data) });
    }
    
    getPDFInvoice(invoiceId) {
        return axios.get('api/v1/invoice/' + invoiceId, {'responseType': 'blob'});
    }
}