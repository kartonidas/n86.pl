import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class UserInvoiceService {
    invoices(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search,
        };
        return axios.get('api/v1/customer-invoices', { params : data });
    }
    
    invoiceCreate(data) {
        return axios.put('api/v1/customer-invoice', removeNullValues(data));
    }
    
    invoiceCreateFromProforma(id, data) {
        return axios.put('api/v1/customer-invoice/from-proforma/' + id, removeNullValues(data));
        
    }
    
    invoiceGet(id) {
        return axios.get('api/v1/customer-invoice/' + id);
    }
    
    invoiceUpdate(id, data) {
        return axios.put('api/v1/customer-invoice/' + id, removeNullValues(data));
    }
    
    invoiceRemove(id) {
        return axios.delete('api/v1/customer-invoice/' + id);
    }
    
    getPDF(id) {
        return axios.get('api/v1/customer-invoice/' + id + "/pdf", {'responseType': 'blob'});
    }
    
    getNumber(saleRegisterId) {
        return axios.get('api/v1/customer-invoice/number/' + saleRegisterId);
    }
    
    saleRegisterList(size, page, search) {
        var data = {
            size: size,
            page: page,
            search: search,
        };
        return axios.get('api/v1/sale-register', { params : data });
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
    
    getInvoiceData() {
        return axios.get('api/v1/customer-invoice/invoice-data');
    }
    
    updateInvoiceData(data) {
        return axios.put('api/v1/customer-invoice/invoice-data', removeNullValues(data));
    }
}