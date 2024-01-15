import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DocumentService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/documents', { params : data });
    }
    
    get(documentId) {
        return axios.get('api/v1/document/' + documentId);
    }
    
    getPDFDocument(documentId) {
        return axios.get('api/v1/document/' + documentId + "/pdf", {'responseType': 'blob'});
    }
    
    update(documentId, documentData) {
        return axios.put('api/v1/document/' + documentId, removeNullValues(documentData));
    }
    
    remove(documentId) {
        return axios.delete('api/v1/document/' + documentId);
    }
}