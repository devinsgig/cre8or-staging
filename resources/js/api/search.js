export function getSearchSuggest(keyword){
    return window.axios.get(`search/suggest?query=${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getSearchResults(search_type, query, type, page){
    return window.axios.get(`search/${search_type}?query=${query}&type=${type}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}