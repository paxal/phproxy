function FindProxyForURL(url, host) {
    // Redirect known traffic through proxy
    if (shExpMatch(host, "google.com"))
        return "PROXY 10.129.22.122:8001";
    if (shExpMatch(host, "*.google.com"))
        return "PROXY 10.129.22.122:8001";


    // DEFAULT RULE: All traffic is direct.
    return "DIRECT";
}
