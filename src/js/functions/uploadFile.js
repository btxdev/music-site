export const uploadFile = (url, data) => {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest()
    // xhr.responseType = 'json';
    xhr.onload = () => {
      console.log('server response:');
      console.log(xhr.response);
      resolve(xhr.response);
    }
    xhr.onerror = () => {
      console.log("network error");
      reject();
    }
    xhr.open('POST', url);
    xhr.send(data);
  });
};
