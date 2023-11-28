<script>
    import { ref } from 'vue'
</script>

<template>
    <div class="text-center">
        {{ $t('register.reset_password_successfull') }}
        
        <div class="mt-4">
            <router-link :to="{name: 'signin'}">
                {{ $t('register.signin') }}
            </router-link>
        </div>
    </div>
</template>