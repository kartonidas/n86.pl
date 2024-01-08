import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DocumentTemplateService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/documents/templates', { params : data });
    }
    
    listGroupByType(size, page) {
        var data = {
            size: size,
            page: page,
        };
        return axios.get('api/v1/documents/templates/group', { params : data });
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