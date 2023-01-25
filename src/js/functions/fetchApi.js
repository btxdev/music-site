export const fetchApi = (url, args) => {
  let newArgs = {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(args),
  };
  ["method", "headers", "body"].forEach((key) => {
    if (key in args) newArgs[key] = args[key];
  });
  return new Promise((resolve, reject) => {
    fetch(url, newArgs)
      .then((response) => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
          return response.json().then((data) => {
            console.log("parsed json data:");
            console.log(data);
            resolve(data);
          });
        } else {
          return response.text().then((text) => {
            console.log("server returns error string:");
            console.log(text);
            reject(text);
          });
        }
      })
      .catch((error) => {
        console.log("server error:");
        console.log(error);
        reject(error);
      });
  });
};
