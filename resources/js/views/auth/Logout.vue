<script setup>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import UserService from '@/service/UserService';

    const userService = new UserService();
    const router = useRouter()
    
    userService.logout()
        .then(
            () => { router.push({name: 'signin'}) },
            () => { router.push({name: 'signin'}) },
        );
</script>

<template></template>