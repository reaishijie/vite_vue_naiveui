import axios from "axios"

const register = axios.create({
    baseURL: 'https://demo.321521.xyz/auth/register'
})
//登录接口 
const login = axios.create({
    baseURL: 'https://demo.321521.xyz/auth/login'
})
// get 获取记录列表   ||    post 新增记录  ||  put 更新  || delete 删除
const records = axios.create({
    baseURL: 'https://demo.321521.xyz/records'
})
//get 获取操作记录
const logs = axios.create({
    baseURL: 'https://demo.321521.xyz/logs'
})

export {
    register,
    login,
    records,
    logs
}