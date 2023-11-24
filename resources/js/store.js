import { defineStore } from 'pinia'

export const appStore = defineStore('store', {
    state: () => {
        return {
            userId: null,
            toastMessage: null,
            permissions: null,
            tableOrder: {},
        };
    },
    actions: {
        setUserId (id) {
            this.userId = id;
        },
        
        setUserPermission (permissions) {
            this.permissions = permissions;
        },
        
        setToastMessage (data) {
            this.toastMessage = data;
        },
        
        setTableOrder (table, column, order) {
            if (this.tableOrder[table] == undefined)
                this.tableOrder[table] = {};
            
            this.tableOrder[table] = {
                'col' : column,
                'dir' : order,
            };
        },
        
        getTableOrder(table) {
            return this.tableOrder[table];
        },
    },
    persist: true,
})
