import axios from "axios"

const register = axios.create({
    baseURL: 'http://localhost:3000/auth/register'
})
//登录接口 
const login = axios.create({
    baseURL: 'http://localhost:3000/auth/login'
})
// get 获取记录列表   ||    post 新增记录  ||  put 更新  || delete 删除
const records = axios.create({
    baseURL: 'http://localhost:3000/records'
})
//get 获取操作记录
const logs = axios.create({
    baseURL: 'http://localhost:3000/logs'
})

export {
    register,
    login,
    records,
    logs
}