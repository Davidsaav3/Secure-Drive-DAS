import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class FileService {
  private apiUrl = 'https://proteccloud.000webhostapp.com/upload.php';

  constructor(private http: HttpClient) {}

  subirArchivo(archivo: File, ruta : any): Observable<any> {
    const formData: FormData = new FormData();
    formData.append('uploaded_file', archivo, archivo.name);
    formData.append('ruta', ruta);

    return this.http.post<any>(this.apiUrl, formData);
  }
}