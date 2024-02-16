import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class PermissionService {
    list(meta) {
        return axios.get('api/v1/permissions', { params : removeNullValues(meta) });
    }
    
    modules() {
        return axios.get('api/v1/permission/modules');
    }
    
    create(name, permissions, is_default) {
        var permissionData = {
            name : name,
            permissions : permissions,
            is_default : is_default,
        };
        return axios.put('api/v1/permission', permissionData);
    }
    
    get(permissionId) {
        return axios.get('api/v1/permission/' + permissionId);
    }
    
    update(permissionId, name, permissions, is_default) {
        var permissionData = {
            name : name,
            permissions : permissions,
            is_default : is_default,
        };
        
        return axios.put('api/v1/permission/' + permissionId, removeNullValues(permissionData));
    }
    
    remove(permissionId) {
        return axios.delete('api/v1/permission/' + permissionId);
    }
}