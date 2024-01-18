import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})

export class DownloaderSharedService {

  constructor(private http: HttpClient) {}

  downloadFile(fileName: string, owner: any, user: any): Observable<any> {
    const url = `https://proteccloud.000webhostapp.com/downloadershared.php`; 
    const headers = new HttpHeaders();
    headers.append('Content-Type', 'application/json');
    const formData = new FormData();
    formData.append('file_name', fileName);
    formData.append('file_owner', owner);
    formData.append('file_shared', user);

    return this.http.post(url, formData, { headers, responseType: 'blob' })
      .pipe(
        catchError((error: HttpErrorResponse) => {
          //console.error('Error al descargar el archivo:', error);
          return throwError('No se pudo descargar el archivo; inténtalo de nuevo más tarde.');
        })
      );
  }

  downloader(fileName: string, owner: any, user: any): void {
    this.downloadFile(fileName, owner, user).subscribe(
      (response: any) => {
        const blob = new Blob([response], { type: 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = fileName;
        link.click();
      },
      (error: any) => {
        //console.error('Error al descargar el archivo:', error);
      }
    );
  }
}
