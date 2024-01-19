import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ReportService {
    itemReport(itemId, period, year) {
        var data = {
            period: period,
            year: year,
        };
        
        return axios.get('api/v1/report/chart/item/' + itemId, { params : removeNullValues(data) });
    }
    
    rentalReport(rentalId, period, year) {
        var data = {
            period: period,
            year: year,
        };
        
        return axios.get('api/v1/report/chart/rental/' + rentalId, { params : removeNullValues(data) });
    }
}