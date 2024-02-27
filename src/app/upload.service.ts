import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class UploadService {
  private apiUrl = 'https://dasapp.alwaysdata.net/upload.php';

  constructor(private http: HttpClient) {}

  upload(archivo: File, ruta : any): Observable<any> {
    const formData: FormData = new FormData();
    //console.log(archivo)
    //console.log(ruta)
    formData.append('uploaded_file', archivo, archivo.name);
    formData.append('ruta', ruta);
    return this.http.post<any>(this.apiUrl, formData)
  }
}