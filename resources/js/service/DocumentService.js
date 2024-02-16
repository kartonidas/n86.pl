import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DocumentService {
    list(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/documents', { params : removeNullValues(data) });
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