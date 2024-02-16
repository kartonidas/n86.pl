import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ItemService {
    settings() {
        return axios.get('api/v1/items/settings');
    }
    
    list(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/items', { params : removeNullValues(data) });
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
    
    bills(itemId, meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/item/' + itemId + "/bills", { params : removeNullValues(data) });
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
    
    cyclicalFees(itemId, meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get('api/v1/item/' + itemId + "/fees", { params : removeNullValues(data) });
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
    
    cyclicalFeeCosts(itemId, feeId, meta) {
        return axios.get('api/v1/item/' + itemId + "/fee/" + feeId + "/costs", { params : removeNullValues(meta) });
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
    
    allBills(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get("api/v1/bills", { params : removeNullValues(data) });
    }
    
    allDeposits(meta, search) {
        let data = Object.assign(meta, { "search" : search });
        return axios.get("api/v1/deposits", { params : removeNullValues(data) });
    }
}