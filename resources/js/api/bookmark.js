export function getBookmarkItem(itemType, page){
    return window.axios.get(`/bookmark/get/${itemType}/${page}`).then((response) => {
        return response.data.data;
    });
}