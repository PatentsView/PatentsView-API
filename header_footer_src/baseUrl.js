const hostname = window.location.hostname

let baseUrl = ""
switch (hostname) {
    case "localhost":
        baseUrl = "https://dev.patentsview.org"
        break
    case "dev.patentsview.org":
        baseUrl = "https://dev.patentsview.org"
        break
    case "www.patentsview.org":
        baseUrl = "https://www.patentsview.org"
        break
    default:
        console.log("Unknown hostname.")
}

export default baseUrl
