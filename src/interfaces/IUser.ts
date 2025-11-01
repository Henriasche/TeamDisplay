export defaultinterface IUser {
    id: number
    name: string    
    avatar: string
    intro: string
    status: string
    periods: Array<{
        from: string
        to: string
        activity: string
    }>
}