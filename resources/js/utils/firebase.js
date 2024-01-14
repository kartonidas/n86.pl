import { initializeApp } from 'firebase/app'
import { getDatabase, ref as dbRef } from 'firebase/database'

const firebaseConfig = {
  apiKey: "AIzaSyC0hPSjdE46HhxvcwqKSyymUPQhb4cTBFk",
  authDomain: "app-n86.firebaseapp.com",
  databaseURL: "https://app-n86-default-rtdb.europe-west1.firebasedatabase.app/",
  projectId: "app-n86",
  storageBucket: "app-n86.appspot.com",
  messagingSenderId: "907796094200",
  appId: "1:907796094200:web:2bb93acdbfab28b17b2f0d"
};

const firebaseApp = initializeApp(firebaseConfig);

const db = getDatabase(firebaseApp)
export default db;
