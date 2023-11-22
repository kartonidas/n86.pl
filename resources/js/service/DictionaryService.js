import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DictionaryService {
    getTypes() {
        return axios.get('api/v1/dictionary/types');
    }
    
    list(page) {
        var data = {
            page: page
        };
        return axios.get('api/v1/dictionaries', data);
    }
    
    listByType(type, size, page) {
        var data = {
            size: size,
            page: page
        };
        return axios.get('api/v1/dictionaries/' + type, data);
    }
    
    create(type, active, name) {
        var dictionaryData = {
            type : type,
            active : active,
            name : name,
        };
        return axios.put('api/v1/dictionary', dictionaryData);
    }
    
    get(itemId) {
        return axios.get('api/v1/dictionary/' + itemId);
    }
    
    update(dictionaryId, active, name) {
        var itemData = {
            active : active,
            name : name,
        };
        
        return axios.put('api/v1/dictionary/' + dictionaryId, removeNullValues(itemData));
    }
    
    remove(dictionaryId) {
        return axios.delete('api/v1/dictionary/' + dictionaryId);
    }
}