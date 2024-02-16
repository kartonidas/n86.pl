import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class TenantService {
    list(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/tenants', { params : removeNullValues(data) });
    }
    
    create(tenantData) {
        return axios.put('api/v1/tenant', tenantData);
    }
    
    get(tenantId) {
        return axios.get('api/v1/tenant/' + tenantId);
    }
    
    update(tenantId, tenantData) {
        return axios.put('api/v1/tenant/' + tenantId, removeNullValues(tenantData));
    }
    
    remove(tenantId) {
        return axios.delete('api/v1/tenant/' + tenantId);
    }
    
    validate(tenantData) {
        return axios.post('api/v1/tenant/validate', removeNullValues(tenantData));
    }
}