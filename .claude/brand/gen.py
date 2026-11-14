import cairosvg, io
from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from PIL import Image
B='/home/claude/pkg/.claude/brand/'
F='node_modules/@fontsource/cormorant-garamond/files/cormorant-garamond-latin-%s.woff'
f600=TTFont(F%'600-normal'); f500i=TTFont(F%'500-italic')
ROSE,GOLD,INK,IVORY,GOLDL='#BE185D','#B8893B','#2B1B22','#FFF8F3','#F2D08A'
def outline(t,font,size,x,y,fill):
    gs=font.getGlyphSet();cm=font.getBestCmap();s=size/font['head'].unitsPerEm;cx=0;p=[]
    for ch in t:
        g=cm[ord(ch)];pen=SVGPathPen(gs);gs[g].draw(pen)
        if pen.getCommands():p.append(f'<path transform="translate({x+cx*s:.2f} {y}) scale({s:.5f} {-s:.5f})" d="{pen.getCommands()}"/>')
        cx+=gs[g].width
    return f'<g fill="{fill}">{"".join(p)}</g>',cx*s
HEART='<path transform="translate(32 31.6) scale(.8) translate(-32 -33.4)" d="M32 38C27.5 34.6 27.2 31.2 29 29.8C30.3 28.8 31.6 29.4 32 30.6C32.4 29.4 33.7 28.8 35 29.8C36.8 31.2 36.5 34.6 32 38Z" fill="{h}"/>'
def mark(a=ROSE,b=GOLD,h=ROSE,sw=3,r=14,ca=24,cb=40,heart=True):
    import math
    dx=(cb-ca)/2;dy=math.sqrt(r*r-dx*dx);ang=math.atan2(dy,dx)
    p=lambda t:(ca+r*math.cos(t),32+r*math.sin(t))
    p1=p(ang+.35);p2=p(ang-.35)
    arc=f'<path d="M{p1[0]:.2f} {p1[1]:.2f}A{r} {r} 0 0 0 {p2[0]:.2f} {p2[1]:.2f}" stroke="{a}"/>'
    return (f'<g fill="none" stroke-width="{sw}" stroke-linecap="round"><circle cx="{ca}" cy="32" r="{r}" stroke="{a}"/><circle cx="{cb}" cy="32" r="{r}" stroke="{b}"/>{arc}</g>'
            +(HEART.replace('{h}',h) if heart else ''))
def svg(vb,body,title='Momenikah',w=None):
    return f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{vb}" role="img" aria-label="{title}"><title>{title}</title>{body}</svg>'
def w(path,s):open(B+path,'w').write(s)
# logos
w('logo/logo-mark.svg',svg('0 0 64 64',mark()))
w('logo/logo-mark-mono.svg',svg('0 0 64 64',mark('currentColor','currentColor','currentColor')))
for name,ink,m in [('',INK,mark()),('-white',IVORY,mark(IVORY,GOLDL,IVORY))]:
    t,tw=outline('Momenikah',f600,52,78,47,ink)
    w(f'logo/logo-horizontal{name}.svg',svg(f'0 0 {78+tw+4:.0f} 64',m+t))
    t,tw=outline('Momenikah',f600,52,0,0,ink)
    ox=(tw-64)/2
    t,_=outline('Momenikah',f600,52,0,112,ink)
    w(f'logo/logo-stacked{name}.svg',svg(f'{-ox*0:.0f} 0 {tw:.0f} 128',f'<g transform="translate({(tw-64)/2:.1f} 0)">{m}</g>'+t))
# favicon
fav=lambda sq,pad=0,heart=False:svg('0 0 64 64',(f'<rect width="64" height="64" rx="{0 if sq else 14}" fill="{ROSE}"/>')+f'<g transform="translate(32 32) scale({1-pad}) translate(-32 -32)">'+mark(IVORY,GOLDL,IVORY,sw=4.6,r=13,ca=23.5,cb=40.5,heart=heart)+'</g>')
w('favicon/favicon.svg',fav(False))
def png(s,size,path):cairosvg.svg2png(bytestring=s.encode(),write_to=B+path,output_width=size,output_height=size)
for n in (16,32,48):png(fav(False),n,f'favicon/favicon-{n}.png')
png(fav(True,0.04,True),180,'favicon/apple-touch-icon.png')
png(fav(False),192,'favicon/android-chrome-192.png');png(fav(False),512,'favicon/android-chrome-512.png')
png(fav(True,0.28),512,'favicon/maskable-512.png')
ims=[Image.open(B+f'favicon/favicon-{n}.png') for n in (48,32,16)]
ims[0].save(B+'favicon/favicon.ico',sizes=[(48,48),(32,32),(16,16)],append_images=ims[1:])
# og image
t1,w1=outline('Momenikah',f600,120,0,0,INK);t2,_=outline('Satu link untuk semua momen nikahmu.',f500i,50,96,430,ROSE)
t1,w1=outline('Momenikah',f600,128,252,330,INK)
bg=f'<rect width="1200" height="630" fill="{IVORY}"/><g opacity=".16" transform="translate(640 -40) scale(9.5)">{mark(heart=False)}</g><g transform="translate(96 214) scale(2.4)">{mark()}</g>'
og=svg('0 0 1200 630',bg+t1+t2,'Momenikah: undangan digital')
cairosvg.svg2png(bytestring=og.encode(),write_to=B+'social/og-default.png',output_width=1200,output_height=630)
w('social/og-default.svg',og)
w('favicon/site.webmanifest','''{"name":"Momenikah","short_name":"Momenikah","description":"Undangan digital untuk pernikahan, walimatul urs, tunangan, khitanan, aqiqah, dan ulang tahun.","lang":"id","start_url":"/","display":"standalone","background_color":"#FFF8F3","theme_color":"#BE185D","icons":[{"src":"/android-chrome-192.png","sizes":"192x192","type":"image/png"},{"src":"/android-chrome-512.png","sizes":"512x512","type":"image/png"},{"src":"/maskable-512.png","sizes":"512x512","type":"image/png","purpose":"maskable"}]}''')
# contact sheet for review
sheet=svg('0 0 900 300',f'<rect width="900" height="300" fill="{IVORY}"/>'+'<g transform="translate(20 20) scale(1.6)">'+open(B+'logo/logo-horizontal.svg').read().replace('<svg','<svg x="0" y="0"',1)+'</g>')
cairosvg.svg2png(bytestring=sheet.encode(),write_to='/tmp/sheet.png',output_width=900)
