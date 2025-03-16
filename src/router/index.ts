import { createRouter, createWebHistory } from 'vue-router'
const routes = [
    {
        name:'home',
        path : "/home",
        alias:['/index','/'],
        component: () => import ('../views/Home.vue')
    },
    {
        name:'test',
        path : "/test",
        component: () => import ('../views/Test.vue')
    },
    {
        name:'register',
        path : "/register",
        component: () => import ('../views/Register.vue')
    },
    {
        name:'login',
        path : "/login",
        component: () => import ('../views/Login.vue')
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router