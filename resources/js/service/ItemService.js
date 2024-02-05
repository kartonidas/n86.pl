import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ItemService {
    settings() {
        return axios.get('api/v1/items/settings');
    }
    
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/items', { params : data });
    }
    
    create(itemData) {
        return axios.put('api/v1/item', removeNullValues(itemData));
    }
    
    get(itemId) {
        return axios.get('api/v1/item/' + itemId);
    }
    
    update(itemId, itemData) {
        return axios.put('api/v1/item/' + itemId, removeNullValues(itemData));
    }
    
    remove(itemId) {
        return axios.delete('api/v1/item/' + itemId);
    }
    
    validate(itemData) {
        return axios.post('api/v1/item/validate', removeNullValues(itemData));
    }
    
    bills(itemId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/item/' + itemId + "/bills", { params : data });
    }
    
    createBill(itemId, billData) {
        return axios.put('api/v1/item/' + itemId + "/bill", removeNullValues(billData));
    }
    
    getBill(itemId, billId) {
        return axios.get('api/v1/item/' + itemId + "/bill/" + billId);
    }
    
    updateBill(itemId, billId, billData) {
        return axios.put('api/v1/item/' + itemId + "/bill/" + billId, removeNullValues(billData));
    }
    
    removeBill(itemId, billId) {
        return axios.delete('api/v1/item/' + itemId + "/bill/" + billId);
    }
    
    paymentBill(itemId, billId, paymentData) {
        return axios.post('api/v1/item/' + itemId + "/bill/" + billId + "/payment", paymentData);
    }
    
    cyclicalFees(itemId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/item/' + itemId + "/fees", { params : data });
    }
    
    createCyclicalFee(itemId, feeData) {
        return axios.put('api/v1/item/' + itemId + "/fee", removeNullValues(feeData));
    }
    
    getCyclicalFee(itemId, feeId) {
        return axios.get('api/v1/item/' + itemId + "/fee/" + feeId);
    }
    
    updateCyclicalFee(itemId, feeId, feeData) {
        return axios.put('api/v1/item/' + itemId + "/fee/" + feeId, removeNullValues(feeData));
    }
    
    removeCyclicalFee(itemId, feeId) {
        return axios.delete('api/v1/item/' + itemId + "/fee/" + feeId);
    }
    
    cyclicalFeeCosts(itemId, feeId, size, page) {
        var data = {
            size: size,
            page: page,
        };
        return axios.get('api/v1/item/' + itemId + "/fee/" + feeId + "/costs", { params : data });
    }
    
    removeCyclicalFeeCost(itemId, feeId, costId) {
        return axios.delete('api/v1/item/' + itemId + "/fee/" + feeId + "/cost/" + costId);
    }
    
    addCyclicalFeeCost(itemId, feeId, costData) {
        return axios.put('api/v1/item/' + itemId + "/fee/" + feeId + "/cost", removeNullValues(costData));
    }
    
    archive(itemId) {
        return axios.post('api/v1/item/' + itemId + "/archive");
    }
    
    lock(itemId) {
        return axios.post('api/v1/item/' + itemId + "/lock");
    }
    
    unlock(itemId) {
        return axios.post('api/v1/item/' + itemId + "/unlock");
    }
}