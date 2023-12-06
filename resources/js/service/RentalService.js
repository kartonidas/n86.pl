import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class RentalService {
    rent(tenant, item, rent) {
        
        var rentData = {
            tenant : tenant,
            item : item,
            rent : rent
        };
        
        return axios.put('api/v1/rental/rent', rentData);
    }
    
    validate(rentalData) {
        return axios.post('api/v1/rental/validate', removeNullValues(rentalData));
    }
}