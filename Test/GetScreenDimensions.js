// Get screen dimensions in CSS pixels
const screenWidth = window.screen.width;
const screenHeight = window.screen.height;

console.log(`Screen size: ${screenWidth} x ${screenHeight}`);

// Get available screen dimensions (excluding taskbars/docks)
const availWidth = window.screen.availWidth;
const availHeight = window.screen.availHeight;

console.log(`Available size: ${availWidth} x ${availHeight}`);

// Get maximum physical pixels (accounting for devicePixelRatio)
const maxPixelWidth = screenWidth * window.devicePixelRatio;
const maxPixelHeight = screenHeight * window.devicePixelRatio;

console.log(`Maximum physical pixels: ${maxPixelWidth} x ${maxPixelHeight}`);