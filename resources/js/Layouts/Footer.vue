<script setup>
import moment from 'moment';
moment.locale('id');
</script>
<template>
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; {{ $page.props.app.name }} {{ moment().format('YYYY') }}</div>
            </div>
        </div>
    </footer>
</template>