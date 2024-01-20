import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class RentalService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/rentals', { params : removeNullValues(data) });
    }
    
    rent(tenant, item, rent) {
        var rentData = {
            tenant : removeNullValues(tenant),
            item : removeNullValues(item),
            rent : removeNullValues(rent)
        };
        
        return axios.put('api/v1/rental/rent', rentData);
    }
    
    get(rentalId) {
        return axios.get('api/v1/rental/' + rentalId);
    }
    
    update(rentalId, rentalData) {
        return axios.put('api/v1/rental/' + rentalId, removeNullValues(rentalData));
    }
    
    validate(rentalData) {
        return axios.post('api/v1/rental/validate', removeNullValues(rentalData));
    }
    
    remove(rentalId) {
        return axios.delete('api/v1/rental/' + rentalId);
    }
    
    terminate(rentalId, terminateData) {
        return axios.post('api/v1/rental/' + rentalId + '/terminate', terminateData);
    }
    
    bills(rentalId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/rental/' + rentalId + '/bills', { params : removeNullValues(data) });
    }
    
    getBill(rentalId, billId) {
        return axios.get('api/v1/rental/' + rentalId + '/bill/' + billId);
    }
    
    createBill(rentalId, billData) {
        return axios.put('api/v1/rental/' + rentalId + "/bill", removeNullValues(billData));
    }
   
    updateBill(rentalId, billId, billData) {
        return axios.put('api/v1/rental/' + rentalId + "/bill/" + billId, removeNullValues(billData));
    }
    
    paymentBill(rentalId, billId, paymentData) {
        return axios.post('api/v1/rental/' + rentalId + "/bill/" + billId + "/payment", paymentData);
    }
    
    removeBill(rentalId, billId) {
        return axios.delete('api/v1/rental/' + rentalId + "/bill/" + billId);
    }
    
    generateTemplateDocument(rentalId, templateType, templateId) {
        var documentData = {
            type: templateType,
            template: templateId
        };
        return axios.post('api/v1/rental/' + rentalId + "/document", documentData);
    }
    
    generateDocument(rentalId, documentData) {
        return axios.put('api/v1/rental/' + rentalId + "/document", documentData);
    }
    
    getDocument(rentalId, documentId) {
        return axios.get('api/v1/rental/' + rentalId + "/document/" + documentId);
    }
    
    updateDocument(rentalId, documentId, documentData) {
        return axios.put('api/v1/rental/' + rentalId + "/document/" + documentId, documentData);
    }
    
    getDocuments(rentalId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/rental/' + rentalId + "/documents", { params : removeNullValues(data) });
    }
    
    removeDocument(rentalId, documentId) {
        return axios.delete('api/v1/rental/' + rentalId + "/document/" + documentId);
    }
    
    getPDFDocument(rentalId, documentId) {
        return axios.get('api/v1/rental/' + rentalId + "/document/" + documentId + "/pdf", {'responseType': 'blob'});
    }
    
    payments(rentalId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/rental/' + rentalId + "/payments", { params : removeNullValues(data) });
    }
    
    removePayment(rentalId, paymentId) {
        return axios.delete('api/v1/rental/' + rentalId + "/payment/" + paymentId);
    }
    
    payment(rentalId, depositData) {
        return axios.put('api/v1/rental/' + rentalId + "/deposit", removeNullValues(depositData));
    }
    
    getNonAvailableDates(itemId) {
        return axios.get('api/v1/rental/item/' + itemId + '/getDates');
    }
}