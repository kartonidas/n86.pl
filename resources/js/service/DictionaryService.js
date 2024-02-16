import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class DictionaryService {
    getTypes() {
        return axios.get('api/v1/dictionary/types');
    }
    
    list(meta) {
        return axios.get('api/v1/dictionaries', { params : removeNullValues(meta) });
    }
    
    listByType(type, meta) {
        return axios.get('api/v1/dictionaries/' + type, { params : removeNullValues(meta) });
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