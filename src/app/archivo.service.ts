import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ArchivoService {
  private apiUrl = 'https://proteccloud.000webhostapp.com/files.php';
  constructor(private http: HttpClient) {}

  getFiles(folderPath: string): Observable<any> {
    const formData = new FormData();
    formData.append('folder_path', folderPath);

    return this.http.post<any>(this.apiUrl, formData);
  }
}