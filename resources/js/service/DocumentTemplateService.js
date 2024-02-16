import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DocumentTemplateService {
    list(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/documents/templates', { params : removeNullValues(data) });
    }
    
    listGroupByType(meta) {
        return axios.get('api/v1/documents/templates/group', { params : removeNullValues(meta) });
    }
    
    create(documentTemplateData) {
        return axios.put('api/v1/documents/template', documentTemplateData);
    }
    
    get(documentId) {
        return axios.get('api/v1/documents/template/' + documentId);
    }
    
    update(documentId, documentTemplateData) {
        return axios.put('api/v1/documents/template/' + documentId, removeNullValues(documentTemplateData));
    }
    
    remove(documentId) {
        return axios.delete('api/v1/documents/template/' + documentId);
    }
}