import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class TenantService {
    list(size, page, sort, order) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
        };
        return axios.get('api/v1/tenants', { params : data });
    }
    
    create(tenantData) {
        return axios.put('api/v1/tenants', tenantData);
    }
    
    get(tenantId) {
        return axios.get('api/v1/tenants/' + tenantId);
    }
    
    update(tenantId, tenantData) {
        return axios.put('api/v1/tenants/' + tenantId, removeNullValues(tenantData));
    }
    
    remove(tenantId) {
        return axios.delete('api/v1/tenants/' + tenantId);
    }
}