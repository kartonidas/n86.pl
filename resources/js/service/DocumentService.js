import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DocumentService {
    list(rentalId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/documents/' + rentalId, { params : data });
    }
    
    create(rentalId, documentTemplateData) {
        return axios.put('api/v1/document/' + rentalId, documentTemplateData);
    }
    
    get(documentId) {
        return axios.get('api/v1/document/' + documentId);
    }
    
    update(documentId, documentData) {
        return axios.put('api/v1/document/' + documentId, removeNullValues(documentData));
    }
    
    remove(documentId) {
        return axios.delete('api/v1/document/' + documentId);
    }
}