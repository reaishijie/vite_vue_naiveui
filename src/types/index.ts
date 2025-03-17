export 
interface IUser {
    username: string,
    password: string,
    email: string
}
export interface IStoreUser {
    id: number | null,
    role:string,
    username: string,
    email: string,
    token: string
}
